<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Hooks
 * ============================================================================
 * 
 * Este arquivo contém os hooks principais do plugin que interceptam
 * as operações de atualização de tickets
 * 
 * @license     GPL v2 ou superior
 * @link        https://github.com/gvcaetano190/keepPending
 * @author      Gabriel Caetano
 * @version     1.0.0
 * ============================================================================
 */

/**
 * Install hook - Função de instalação do plugin
 * 
 * @return boolean
 */
function plugin_keeppending_install() {
    global $DB;
    
    // Criar tabela de configuração do plugin
    if (!$DB->tableExists('glpi_plugin_keeppending_config')) {
        $query = "CREATE TABLE `glpi_plugin_keeppending_config` (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `enable_keep_pending` tinyint(1) DEFAULT 1 COMMENT 'Habilitar manter status pendente',
            `enable_logs` tinyint(1) DEFAULT 1 COMMENT 'Habilitar logs',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $DB->query($query);
        
        // Inserir configurações padrão
        if ($DB->tableExists('glpi_plugin_keeppending_config')) {
            $DB->insert('glpi_plugin_keeppending_config', [
                'enable_keep_pending' => 1,
                'enable_logs'         => 1
            ]);
        }
    }
    
    return true;
}

/**
 * Uninstall hook - Função de desinstalação do plugin
 * 
 * @return boolean
 */
function plugin_keeppending_uninstall() {
    global $DB;
    
    // Remover tabela de configuração
    if ($DB->tableExists('glpi_plugin_keeppending_config')) {
        $DB->query("DROP TABLE `glpi_plugin_keeppending_config`");
    }
    
    return true;
}

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
function plugin_keeppending_pre_item_update($item) {
    // DEBUG: Log para arquivo dedicado
    $debug_file = GLPI_LOG_DIR . '/keeppending.log';
    $timestamp = date('Y-m-d H:i:s');
    
    // Verificar se é um ticket (chamado)
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    file_put_contents($debug_file, "[$timestamp] Hook chamado para Ticket\n", FILE_APPEND);
    
    // Verificar se o plugin está habilitado
    if (!plugin_keeppending_isEnabled()) {
        file_put_contents($debug_file, "[$timestamp] Plugin DESABILITADO - saindo\n", FILE_APPEND);
        return;
    }
    
    file_put_contents($debug_file, "[$timestamp] Plugin habilitado\n", FILE_APPEND);
    
    // Obter o ID do ticket
    $ticket_id = $item->getID();
    if (!$ticket_id) {
        file_put_contents($debug_file, "[$timestamp] Ticket ID não encontrado - saindo\n", FILE_APPEND);
        return;
    }
    
    file_put_contents($debug_file, "[$timestamp] Ticket ID: $ticket_id\n", FILE_APPEND);
    
    // Obter dados atuais do ticket do banco de dados
    global $DB;
    $result = $DB->request([
        'SELECT' => ['status'],
        'FROM'   => 'glpi_tickets',
        'WHERE'  => ['id' => $ticket_id]
    ]);
    
    if (!$result->count()) {
        file_put_contents($debug_file, "[$timestamp] Ticket não encontrado no BD - saindo\n", FILE_APPEND);
        return;
    }
    
    $current_data = $result->current();
    $current_status = (int) $current_data['status'];
    
    // Status de Pendente em GLPI = 4 (CommonITILObject::WAITING)
    // INCOMING=1, ASSIGNED=2, PLANNED=3, WAITING=4, SOLVED=5, CLOSED=6
    $PENDING_STATUS = 4;
    
    file_put_contents($debug_file, "[$timestamp] Status atual no BD: $current_status (Pendente=4)\n", FILE_APPEND);
    
    // Log do input recebido
    $input_status = isset($item->input['status']) ? $item->input['status'] : 'não definido';
    file_put_contents($debug_file, "[$timestamp] Status no input: $input_status\n", FILE_APPEND);
    file_put_contents($debug_file, "[$timestamp] Campos no input: " . implode(', ', array_keys($item->input)) . "\n", FILE_APPEND);
    
    // Se o ticket está atualmente em status "Pendente" (status = 4)
    if ($current_status === $PENDING_STATUS) {
        file_put_contents($debug_file, "[$timestamp] ✓ Ticket está em PENDENTE\n", FILE_APPEND);
        
        // Verificar se o status está sendo alterado
        $new_status = isset($item->input['status']) ? (int) $item->input['status'] : $current_status;
        
        file_put_contents($debug_file, "[$timestamp] Novo status solicitado: $new_status\n", FILE_APPEND);
        
        if ($new_status !== $PENDING_STATUS) {
            file_put_contents($debug_file, "[$timestamp] ⚠ Tentativa de mudar status de $current_status para $new_status\n", FILE_APPEND);
            
            // Detectar se é uma mudança MANUAL (direta do campo status)
            // ou se é uma mudança automática (via resposta, email, etc)
            $is_manual = plugin_keeppending_isManualStatusChange($item);
            file_put_contents($debug_file, "[$timestamp] É mudança manual? " . ($is_manual ? 'SIM' : 'NÃO') . "\n", FILE_APPEND);
            
            if ($is_manual) {
                // É uma mudança MANUAL - PERMITIR (não faz nada)
                file_put_contents($debug_file, "[$timestamp] ✓ PERMITIDO - mudança manual\n", FILE_APPEND);
                plugin_keeppending_log(
                    $ticket_id,
                    'Mudança MANUAL de status permitida',
                    sprintf('Status alterado manualmente: %d → %d', $current_status, $new_status)
                );
                return;
            } else {
                // É uma mudança AUTOMÁTICA (resposta, email) - BLOQUEAR
                file_put_contents($debug_file, "[$timestamp] ✗ BLOQUEADO - mudança automática! Mantendo status 4\n", FILE_APPEND);
                $item->input['status'] = $PENDING_STATUS;
                
                // Registrar a ação no log
                plugin_keeppending_log(
                    $ticket_id,
                    'Mudança automática de status BLOQUEADA',
                    sprintf(
                        'Interação detectada. Status mantido em Pendente: %d → %d (bloqueado)',
                        $current_status,
                        $new_status
                    )
                );
            }
        } else {
            file_put_contents($debug_file, "[$timestamp] Status não está mudando - nada a fazer\n", FILE_APPEND);
        }
    } else {
        file_put_contents($debug_file, "[$timestamp] Ticket NÃO está em Pendente (status=$current_status) - ignorando\n", FILE_APPEND);
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
function plugin_keeppending_item_update($item) {
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    if (!plugin_keeppending_isEnabled()) {
        return;
    }
}

/**
 * Verifica se é uma mudança MANUAL de status (feita pelo usuário diretamente)
 * ou uma mudança automática (via resposta, email, workflow)
 * 
 * Mudanças MANUAIS: 
 * - Usuário vai em "Editar Ticket" e muda o status diretamente
 * - APENAS campos de metadados são alterados (status, urgency, etc)
 * 
 * Mudanças AUTOMÁTICAS: 
 * - Respostas, emails, automações que alteram status junto com conteúdo
 * - Quando um followup/resposta é adicionado junto com mudança de status
 * 
 * @param object $item Objeto Ticket
 * @return bool true se é mudança manual, false se é automática
 */
function plugin_keeppending_isManualStatusChange($item) {
    $input = $item->input;
    
    // Campos que indicam uma INTERAÇÃO/RESPOSTA (automática)
    // Se algum destes estiver presente, é uma mudança automática
    $interaction_fields = [
        '_itil_followup',       // Seguimento adicionado
        'content',              // Conteúdo de resposta
        '_content',             // Conteúdo alternativo
        'solution',             // Solução
        '_solution',            // Solução alternativa
        '_tasktemplates_id',    // Template de tarefa
        '_task',                // Tarefa
        '_validation',          // Validação
        'pending',              // Marcação de pendente via interface
        '_pending',             // Pendente alternativo
    ];
    
    // Verificar $_POST também, pois algumas interações vêm de lá
    $post_interaction_fields = [
        'content',
        'add_followup',
        'add_task',
        'add_solution',
    ];
    
    // Verificar se há campos de interação no input
    foreach ($interaction_fields as $field) {
        if (isset($input[$field]) && !empty($input[$field])) {
            // Há uma interação - é automático
            return false;
        }
    }
    
    // Verificar POST
    foreach ($post_interaction_fields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            return false;
        }
    }
    
    // Verificar se há arrays de followups sendo adicionados
    if (isset($input['_itil_followup']) || isset($input['ITILFollowup'])) {
        return false;
    }
    
    // Se chegou aqui, é uma mudança manual
    return true;
}

/**
 * Verifica se o plugin está habilitado
 * 
 * @return bool true se plugin está habilitado
 */
function plugin_keeppending_isEnabled() {
    global $DB;
    
    if (!$DB->tableExists('glpi_plugin_keeppending_config')) {
        return false;
    }
    
    $result = $DB->request([
        'SELECT' => ['enable_keep_pending'],
        'FROM'   => 'glpi_plugin_keeppending_config',
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
function plugin_keeppending_log($ticket_id, $action, $details = '') {
    global $DB;
    
    // Verificar se logs estão habilitados
    if (!$DB->tableExists('glpi_plugin_keeppending_config')) {
        return;
    }
    
    $result = $DB->request([
        'SELECT' => ['enable_logs'],
        'FROM'   => 'glpi_plugin_keeppending_config',
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
