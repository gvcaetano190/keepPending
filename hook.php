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
            `enable_keep_solved` tinyint(1) DEFAULT 1 COMMENT 'Habilitar manter status solucionado',
            `enable_logs` tinyint(1) DEFAULT 1 COMMENT 'Habilitar logs',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $DB->query($query);
        
        // Inserir configurações padrão
        if ($DB->tableExists('glpi_plugin_keeppending_config')) {
            $DB->insert('glpi_plugin_keeppending_config', [
                'enable_keep_pending' => 1,
                'enable_keep_solved'  => 1,
                'enable_logs'         => 1
            ]);
        }
    } else {
        // Adicionar coluna enable_keep_solved se não existir (para upgrades)
        if (!$DB->fieldExists('glpi_plugin_keeppending_config', 'enable_keep_solved')) {
            $DB->query("ALTER TABLE `glpi_plugin_keeppending_config` ADD `enable_keep_solved` tinyint(1) DEFAULT 1 COMMENT 'Habilitar manter status solucionado' AFTER `enable_keep_pending`");
            $DB->update('glpi_plugin_keeppending_config', ['enable_keep_solved' => 1], [1]);
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
    
    // Status em GLPI: INCOMING=1, ASSIGNED=2, PLANNED=3, WAITING=4, SOLVED=5, CLOSED=6
    $PENDING_STATUS = 4;  // Pendente
    $SOLVED_STATUS = 5;   // Solucionado
    
    // Verificar quais status proteger baseado na configuração
    $protected_statuses = plugin_keeppending_getProtectedStatuses();
    
    file_put_contents($debug_file, "[$timestamp] Status atual no BD: $current_status (Pendente=4, Solucionado=5)\n", FILE_APPEND);
    file_put_contents($debug_file, "[$timestamp] Status protegidos: " . implode(', ', $protected_statuses) . "\n", FILE_APPEND);
    
    // Log do input recebido
    $input_status = isset($item->input['status']) ? $item->input['status'] : 'não definido';
    file_put_contents($debug_file, "[$timestamp] Status no input: $input_status\n", FILE_APPEND);
    file_put_contents($debug_file, "[$timestamp] Campos no input: " . implode(', ', array_keys($item->input)) . "\n", FILE_APPEND);
    
    // Se o ticket está em um dos status protegidos (Pendente ou Solucionado)
    if (in_array($current_status, $protected_statuses)) {
        $status_name = ($current_status === $PENDING_STATUS) ? 'PENDENTE' : 'SOLUCIONADO';
        file_put_contents($debug_file, "[$timestamp] ✓ Ticket está em $status_name\n", FILE_APPEND);
        
        // Verificar se o status está sendo alterado
        $new_status = isset($item->input['status']) ? (int) $item->input['status'] : $current_status;
        
        file_put_contents($debug_file, "[$timestamp] Novo status solicitado: $new_status\n", FILE_APPEND);
        
        if ($new_status !== $current_status) {
            file_put_contents($debug_file, "[$timestamp] ⚠ Tentativa de mudar status de $current_status para $new_status\n", FILE_APPEND);
            
            // Detectar se é uma mudança MANUAL (direta do campo status)
            // ou se é uma mudança automática (via resposta, email, etc)
            $is_manual = plugin_keeppending_isManualStatusChange($item);
            file_put_contents($debug_file, "[$timestamp] É mudança manual? " . ($is_manual ? 'SIM' : 'NÃO') . "\n", FILE_APPEND);
            
            // Status FECHADO = 6 - Sempre permitir fechamento automático (cron após 24h)
            $CLOSED_STATUS = 6;
            
            if ($new_status === $CLOSED_STATUS) {
                // Mudança para FECHADO - SEMPRE PERMITIR (cron de fechamento automático)
                file_put_contents($debug_file, "[$timestamp] ✓ PERMITIDO - fechamento automático do ticket\n", FILE_APPEND);
                plugin_keeppending_log(
                    $ticket_id,
                    'Fechamento automático PERMITIDO',
                    sprintf('Status alterado para Fechado: %d → %d (fechamento automático)', $current_status, $new_status)
                );
                return;
            }
            
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
                // É uma mudança AUTOMÁTICA (resposta, email)
                
                // Se estava SOLUCIONADO, mudar para PENDENTE
                if ($current_status === $SOLVED_STATUS) {
                    file_put_contents($debug_file, "[$timestamp] ↪ REDIRECIONADO - Solucionado → Pendente (em vez de Em atendimento)\n", FILE_APPEND);
                    $item->input['status'] = $PENDING_STATUS;
                    
                    plugin_keeppending_log(
                        $ticket_id,
                        'Status REDIRECIONADO para Pendente',
                        sprintf(
                            'Interação em ticket Solucionado. Status alterado: %d → %d (Pendente)',
                            $current_status,
                            $PENDING_STATUS
                        )
                    );
                } else {
                    // Se estava PENDENTE, manter PENDENTE
                    file_put_contents($debug_file, "[$timestamp] ✗ BLOQUEADO - mudança automática! Mantendo status $current_status\n", FILE_APPEND);
                    $item->input['status'] = $current_status;
                    
                    plugin_keeppending_log(
                        $ticket_id,
                        'Mudança automática de status BLOQUEADA',
                        sprintf(
                            'Interação detectada. Status mantido em %s: %d → %d (bloqueado)',
                            $status_name,
                            $current_status,
                            $new_status
                        )
                    );
                }
            }
        } else {
            file_put_contents($debug_file, "[$timestamp] Status não está mudando - nada a fazer\n", FILE_APPEND);
        }
    } else {
        file_put_contents($debug_file, "[$timestamp] Ticket NÃO está em status protegido (status=$current_status) - ignorando\n", FILE_APPEND);
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
 * ou uma mudança automática (via email/cron)
 * 
 * Mudanças MANUAIS: 
 * - Tem HTTP_REFERER (veio de uma página web)
 * - OU tem CSRF token (formulário web)
 * 
 * Mudanças AUTOMÁTICAS: 
 * - Não tem HTTP_REFERER E não tem CSRF token (cron/email)
 * 
 * @param object $item Objeto Ticket
 * @return bool true se é mudança manual, false se é automática (email/cron)
 */
function plugin_keeppending_isManualStatusChange($item) {
    $input = $item->input;
    $debug_file = GLPI_LOG_DIR . '/keeppending.log';
    $timestamp = date('Y-m-d H:i:s');
    
    // Verificar flags explícitos de email primeiro
    if (isset($input['_mailgate'])) {
        file_put_contents($debug_file, "[$timestamp] Detectado: _mailgate - AUTOMÁTICO\n", FILE_APPEND);
        return false;
    }
    
    if (isset($input['_from_email'])) {
        file_put_contents($debug_file, "[$timestamp] Detectado: _from_email - AUTOMÁTICO\n", FILE_APPEND);
        return false;
    }
    
    // Verificar HTTP_REFERER - interface web sempre tem
    $has_referer = isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']);
    file_put_contents($debug_file, "[$timestamp] HTTP_REFERER: " . ($has_referer ? $_SERVER['HTTP_REFERER'] : 'NENHUM') . "\n", FILE_APPEND);
    
    // Verificar CSRF token - formulário web sempre tem
    $has_csrf = isset($_POST['_glpi_csrf_token']) || isset($input['_glpi_csrf_token']);
    file_put_contents($debug_file, "[$timestamp] CSRF token: " . ($has_csrf ? 'SIM' : 'NÃO') . "\n", FILE_APPEND);
    
    // Se tem referer OU csrf, é interface web = MANUAL
    if ($has_referer || $has_csrf) {
        file_put_contents($debug_file, "[$timestamp] Indicadores de interface web - MANUAL\n", FILE_APPEND);
        return true; // MANUAL
    }
    
    // Sem referer E sem csrf = cron/email = AUTOMÁTICO
    file_put_contents($debug_file, "[$timestamp] Sem referer e sem CSRF - AUTOMÁTICO (cron/email)\n", FILE_APPEND);
    return false; // AUTOMÁTICO
}

/**
 * Retorna lista de status protegidos pelo plugin
 * 
 * @return array Lista de status IDs que devem ser protegidos
 */
function plugin_keeppending_getProtectedStatuses() {
    global $DB;
    
    $protected = [];
    
    // Status: WAITING=4 (Pendente), SOLVED=5 (Solucionado)
    $PENDING_STATUS = 4;
    $SOLVED_STATUS = 5;
    
    if (!$DB->tableExists('glpi_plugin_keeppending_config')) {
        // Padrão: proteger ambos
        return [$PENDING_STATUS, $SOLVED_STATUS];
    }
    
    $result = $DB->request([
        'SELECT' => ['enable_keep_pending', 'enable_keep_solved'],
        'FROM'   => 'glpi_plugin_keeppending_config',
        'LIMIT'  => 1
    ]);
    
    if (!$result->count()) {
        return [$PENDING_STATUS, $SOLVED_STATUS];
    }
    
    $config = $result->current();
    
    if ((bool) ($config['enable_keep_pending'] ?? true)) {
        $protected[] = $PENDING_STATUS;
    }
    
    if ((bool) ($config['enable_keep_solved'] ?? true)) {
        $protected[] = $SOLVED_STATUS;
    }
    
    return $protected;
}

/**
 * Verifica se o plugin está habilitado (pelo menos um status protegido)
 * 
 * @return bool true se plugin está habilitado
 */
function plugin_keeppending_isEnabled() {
    $protected = plugin_keeppending_getProtectedStatuses();
    return count($protected) > 0;
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
    
    // Usar Toolbox::logInFile ao invés de Event::log (mais simples e compatível)
    $message = sprintf(
        'Ticket #%d - %s%s',
        $ticket_id,
        $action,
        $details ? ' - ' . $details : ''
    );
    Toolbox::logInFile('keeppending', $message . "\n");
}
