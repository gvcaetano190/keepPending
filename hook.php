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
    // Verificar se é um ticket (chamado)
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    // Verificar se o plugin está habilitado
    if (!plugin_keeppending_isEnabled()) {
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
            if (plugin_keeppending_isManualStatusChange($item)) {
                // É uma mudança MANUAL - PERMITIR (não faz nada)
                return;
            } else {
                // É uma mudança AUTOMÁTICA (resposta, email) - BLOQUEAR
                $item->input['status'] = $PENDING_STATUS;
                
                // Registrar a ação no log
                plugin_keeppending_log(
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
 * - APENAS o campo status é alterado
 * 
 * Mudanças AUTOMÁTICAS: 
 * - Respostas, emails, automações que alteram status
 * - Múltiplos campos são alterados (content, followups, etc)
 * 
 * @param object $item Objeto Ticket
 * @return bool true se é mudança manual, false se é automática
 */
function plugin_keeppending_isManualStatusChange($item) {
    // Verificar se há campos adicionais sendo alterados além do status
    // Se APENAS status está mudando, é provável que seja manual
    
    $input = $item->input;
    $manual_fields = ['status']; // Campo direto de mudança manual
    
    // Campos relacionados a respostas automáticas/interações
    $auto_fields = [
        'content',          // Resposta/comentário
        'solutions_id',     // Solução registrada
        'actiontime',       // Tempo de ação registrado
        'followups',        // Seguimentos
        'date_mod',         // Data de modificação automática
        'users_id_lastupdater', // Quem atualizou por último
    ];
    
    // Verificar se há mudanças além de status (indica automação)
    $has_auto_changes = false;
    foreach ($auto_fields as $field) {
        if (isset($input[$field]) && $input[$field] != '') {
            $has_auto_changes = true;
            break;
        }
    }
    
    // Se há mudanças de conteúdo/resposta, é automático
    if ($has_auto_changes) {
        return false; // Não é manual, é automático
    }
    
    // Se APENAS status mudou, é manual
    $changed_fields = array_keys($input);
    if (count($changed_fields) === 1 && in_array('status', $changed_fields)) {
        return true; // É manual - apenas status foi alterado
    }
    
    // Se apenas status e alguns metadados mudaram, é manual
    $safe_fields = ['date_mod', '_job', '_no_history'];
    $other_fields = array_diff($changed_fields, ['status'], $safe_fields);
    if (empty($other_fields)) {
        return true; // É manual
    }
    
    // Caso padrão: considerar automático para permitir respostas
    return false;
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
