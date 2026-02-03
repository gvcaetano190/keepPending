<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Hooks
 * ============================================================================
 * 
 * Este arquivo contém os hooks principais do plugin que interceptam
 * as operações de atualização de tickets
 * 
 * ============================================================================
 */

/**
 * Hook PRÉ-ATUALIZAÇÃO: Intercepta antes de salvar mudanças no banco de dados
 * 
 * Este hook é executado ANTES da atualização ser salva, para:
 * 
 * BLOQUEAR (manter Pendente):
 * - Mudanças automáticas de status via respostas, emails, interações
 * - Impede que GLPI mude automaticamente para "Em Atendimento"
 * 
 * PERMITIR (não interfere):
 * - Mudanças manuais diretas do campo status pelo usuário
 * - Permite que técnicos/gestores mudem o status quando necessário
 * 
 * @param object $item Objeto Ticket que será atualizado
 * @return void
 */
function plugin_keepPending_pre_item_update($item) {
    // Verificar se é um ticket (chamado)
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    // Verificar se o plugin está habilitado
    if (!plugin_keepPending_isEnabled()) {
        return;
    }
    
    // Obter o ID do ticket
    $ticket_id = $item->getID();
    if (!$ticket_id) {
        return;
    }
    
    // Obter dados atuais do ticket do banco de dados
    global $DB;
    $result = $DB->request([
        'SELECT' => ['status'],
        'FROM'   => 'glpi_tickets',
        'WHERE'  => ['id' => $ticket_id]
    ]);
    
    if (!$result->count()) {
        return;
    }
    
    $current_data = $result->current();
    $current_status = $current_data['status'];
    
    // Status de Pendente em GLPI = 5
    $PENDING_STATUS = 5;
    
    // Se o ticket está atualmente em status "Pendente" (status = 5)
    if ($current_status == $PENDING_STATUS) {
        // Verificar se o status está sendo alterado
        if (isset($item->input['status']) && $item->input['status'] != $PENDING_STATUS) {
            
            // Detectar se é uma mudança MANUAL (direta do campo status)
            // ou se é uma mudança automática (via resposta, email, etc)
            if (plugin_keepPending_isManualStatusChange($item)) {
                // É uma mudança MANUAL - PERMITIR (não faz nada)
                return;
            } else {
                // É uma mudança AUTOMÁTICA (resposta, email) - BLOQUEAR
                $item->input['status'] = $PENDING_STATUS;
                
                // Registrar a ação no log
                plugin_keepPending_log(
                    $ticket_id,
                    'Mudança automática de status bloqueada',
                    sprintf(
                        'Interação detectada. Status mantido em Pendente: %s → %s',
                        $current_status,
                        $_POST['status'] ?? 'desconhecido'
                    )
                );
            }
        }
    }
}

/**
 * Hook PÓS-ATUALIZAÇÃO: Executado após salvar a atualização
 * 
 * Usado para registros e validações finais
 * 
 * @param object $item Objeto Ticket que foi atualizado
 * @return void
 */
function plugin_keepPending_item_update($item) {
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    if (!plugin_keepPending_isEnabled()) {
        return;
    }
}

/**
 * Verifica se é uma mudança MANUAL de status (feita pelo usuário diretamente)
 * ou uma mudança automática (via resposta, email, workflow)
 * 
 * Mudanças MANUAIS: 
 * - Usuário vai em "Editar Ticket" e muda o status diretamente
 * - Usuário usa dropdown de status no formulário do ticket
 * - Origem via interface web (HTTP_REFERER contém ticket.form.php)
 * 
 * Mudanças AUTOMÁTICAS: 
 * - Respostas por email (MailCollector)
 * - Automações de workflow/regras
 * - APIs sem interação de usuário
 * 
 * @param object $item Objeto Ticket
 * @return bool true se é mudança manual, false se é automática
 */
function plugin_keepPending_isManualStatusChange($item) {
    $input = $item->input;
    
    // 1. Se veio do MailCollector ou API interna, é automático
    if (isset($input['_mailgate']) || isset($input['_from_email'])) {
        return false; // Mudança via email - AUTOMÁTICO
    }
    
    // 2. Se é requisição via interface web (tem HTTP_REFERER do GLPI), é manual
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = $_SERVER['HTTP_REFERER'];
        // Se vem do formulário do ticket, é mudança manual
        if (strpos($referer, 'ticket.form.php') !== false ||
            strpos($referer, 'front/ticket.php') !== false) {
            return true; // Mudança via interface web - MANUAL
        }
    }
    
    // 3. Se é POST direto no formulário com _glpi_csrf_token, é interação humana
    if (isset($_POST['_glpi_csrf_token']) || isset($input['_glpi_csrf_token'])) {
        return true; // Mudança com CSRF token - MANUAL (usuário logado)
    }
    
    // 4. Se há campos de conteúdo sendo alterados JUNTO com status na mesma requisição
    // isso indica que o status está mudando como efeito colateral
    $auto_content_fields = [
        'content',          // Conteúdo de resposta
        '_do_not_recompute_takeintoaccount', // Flag de automação
    ];
    
    foreach ($auto_content_fields as $field) {
        if (isset($input[$field]) && !empty($input[$field])) {
            return false; // Tem conteúdo junto - AUTOMÁTICO
        }
    }
    
    // 5. Verificar se APENAS status (e campos seguros) estão mudando
    $safe_fields = ['status', 'id', 'date_mod', '_job', '_no_history', '_glpi_csrf_token', 
                    '_read_date_mod', '_tasktemplates_id', '_actors', 'items_id'];
    $changed_fields = array_keys($input);
    $other_fields = array_diff($changed_fields, $safe_fields);
    
    if (empty($other_fields)) {
        return true; // Apenas campos seguros - MANUAL
    }
    
    // 6. Caso padrão: se chegou até aqui com sessão válida, é manual
    if (Session::getLoginUserID()) {
        return true; // Usuário logado - MANUAL
    }
    
    // Fallback: considerar automático para segurança
    return false;
}

/**
 * Verifica se o plugin está habilitado
 * 
 * @return bool true se plugin está habilitado
 */
function plugin_keepPending_isEnabled() {
    global $DB;
    
    if (!$DB->tableExists('glpi_plugin_keepPending_config')) {
        return false;
    }
    
    $result = $DB->request([
        'SELECT' => ['enable_keep_pending'],
        'FROM'   => 'glpi_plugin_keepPending_config',
        'LIMIT'  => 1
    ]);
    
    if (!$result->count()) {
        return true; // Habilitado por padrão
    }
    
    $config = $result->current();
    return (bool) $config['enable_keep_pending'];
}

/**
 * Registra ações do plugin no banco de dados para auditoria
 * 
 * @param int $ticket_id ID do ticket
 * @param string $action Ação realizada
 * @param string $details Detalhes da ação
 * @return void
 */
function plugin_keepPending_log($ticket_id, $action, $details = '') {
    global $DB;
    
    // Verificar se logs estão habilitados
    if (!$DB->tableExists('glpi_plugin_keepPending_config')) {
        return;
    }
    
    $result = $DB->request([
        'SELECT' => ['enable_logs'],
        'FROM'   => 'glpi_plugin_keepPending_config',
        'LIMIT'  => 1
    ]);
    
    if (!$result->count()) {
        return;
    }
    
    $config = $result->current();
    if (!(bool) $config['enable_logs']) {
        return;
    }
    
    // Registrar no log de eventos do GLPI
    Event::log(
        $ticket_id,
        'Ticket',
        4, // type log (modificação)
        'keepPending',
        sprintf(
            '%s: %s %s',
            $action,
            $details ? '- ' . $details : ''
        )
    );
}
