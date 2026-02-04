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

// Arquivo de log para debug
define('KEEPPENDING_LOG_FILE', GLPI_LOG_DIR . '/keeppending.log');

/**
 * Escreve no log de debug
 */
function plugin_keeppending_debug_log($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}\n";
    file_put_contents(KEEPPENDING_LOG_FILE, $log_message, FILE_APPEND | LOCK_EX);
}

/**
 * Hook PRÉ-ATUALIZAÇÃO: Intercepta antes de salvar mudanças no banco de dados
 * 
 * @param object $item Objeto Ticket que será atualizado
 * @return void
 */
function plugin_keeppending_pre_item_update($item) {
    // Verificar se é um ticket (chamado)
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    $ticket_id = $item->getID();
    plugin_keeppending_debug_log("=== PRE_ITEM_UPDATE Ticket #{$ticket_id} ===");
    
    // Verificar se o plugin está habilitado
    if (!plugin_keeppending_isEnabled()) {
        plugin_keeppending_debug_log("Plugin DESABILITADO - saindo");
        return;
    }
    
    plugin_keeppending_debug_log("Plugin HABILITADO");
    
    if (!$ticket_id) {
        plugin_keeppending_debug_log("Sem ticket_id - saindo");
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
        plugin_keeppending_debug_log("Ticket não encontrado no banco - saindo");
        return;
    }
    
    $current_data = $result->current();
    $current_status = (int)$current_data['status'];
    
    // Status em GLPI
    // 1=Novo, 2=Em atendimento, 3=Planejado, 4=Pendente, 5=Solucionado, 6=Fechado
    $PENDING_STATUS = 4;
    $SOLVED_STATUS = 5;
    $ASSIGNED_STATUS = 2; // Em atendimento
    
    plugin_keeppending_debug_log("Status atual no banco: {$current_status}");
    plugin_keeppending_debug_log("Status Pendente: {$PENDING_STATUS}, Solucionado: {$SOLVED_STATUS}");
    
    // Log do input recebido
    $input_status = isset($item->input['status']) ? $item->input['status'] : 'não definido';
    plugin_keeppending_debug_log("Status no input: {$input_status}");
    plugin_keeppending_debug_log("Campos no input: " . implode(', ', array_keys($item->input)));
    
    // ========================================================================
    // REGRA 1: Ticket em PENDENTE - bloquear mudanças automáticas
    // ========================================================================
    if ($current_status == $PENDING_STATUS) {
        plugin_keeppending_debug_log("✓ Ticket ESTÁ em Pendente");
        
        // Verificar se o status está sendo alterado
        if (isset($item->input['status']) && $item->input['status'] != $PENDING_STATUS) {
            $new_status = $item->input['status'];
            plugin_keeppending_debug_log("⚠ Tentativa de mudar status de {$current_status} para {$new_status}");
            
            // Detectar se é uma mudança MANUAL ou AUTOMÁTICA
            $is_manual = plugin_keeppending_isManualStatusChange($item);
            plugin_keeppending_debug_log("É mudança manual? " . ($is_manual ? "SIM" : "NÃO"));
            
            if ($is_manual) {
                // É uma mudança MANUAL - PERMITIR
                plugin_keeppending_debug_log("✓ PERMITIDO - mudança manual");
                return;
            } else {
                // É uma mudança AUTOMÁTICA - BLOQUEAR
                plugin_keeppending_debug_log("✗ BLOQUEADO - mudança automática! Mantendo status {$PENDING_STATUS}");
                $item->input['status'] = $PENDING_STATUS;
                
                // Registrar a ação no log do GLPI
                Event::log(
                    $ticket_id,
                    'Ticket',
                    4,
                    'keeppending',
                    "Mudança automática de status BLOQUEADA - Status mantido em PENDENTE: {$current_status} → {$new_status} (bloqueado)"
                );
            }
        } else {
            plugin_keeppending_debug_log("Status não está sendo alterado ou já é Pendente");
        }
    }
    // ========================================================================
    // REGRA 2: Ticket em SOLUCIONADO - redirecionar para PENDENTE (não Em atendimento)
    // ========================================================================
    else if ($current_status == $SOLVED_STATUS) {
        plugin_keeppending_debug_log("✓ Ticket ESTÁ em Solucionado");
        
        // Verificar se está tentando mudar para "Em atendimento" (2)
        if (isset($item->input['status']) && $item->input['status'] == $ASSIGNED_STATUS) {
            plugin_keeppending_debug_log("⚠ Tentativa de mudar de Solucionado para Em Atendimento");
            
            // Detectar se é uma mudança MANUAL ou AUTOMÁTICA
            $is_manual = plugin_keeppending_isManualStatusChange($item);
            plugin_keeppending_debug_log("É mudança manual? " . ($is_manual ? "SIM" : "NÃO"));
            
            if ($is_manual) {
                // É uma mudança MANUAL - PERMITIR
                plugin_keeppending_debug_log("✓ PERMITIDO - mudança manual");
                return;
            } else {
                // É uma mudança AUTOMÁTICA - REDIRECIONAR para PENDENTE
                plugin_keeppending_debug_log("→ REDIRECIONANDO para Pendente em vez de Em Atendimento");
                $item->input['status'] = $PENDING_STATUS;
                
                // Marcar para correção pós-update (caso o GLPI ignore nosso input)
                global $KEEPPENDING_FORCE_PENDING;
                $KEEPPENDING_FORCE_PENDING[$ticket_id] = $PENDING_STATUS;
                plugin_keeppending_debug_log("→ Marcado ticket #{$ticket_id} para forçar Pendente no pós-update");
                
                // Registrar a ação no log do GLPI
                Event::log(
                    $ticket_id,
                    'Ticket',
                    4,
                    'keeppending',
                    "Resposta em ticket Solucionado - Redirecionado para PENDENTE: {$current_status} → {$ASSIGNED_STATUS} → {$PENDING_STATUS}"
                );
            }
        } else {
            plugin_keeppending_debug_log("Mudança de status diferente de Em Atendimento - ignorando");
        }
    } else {
        plugin_keeppending_debug_log("Ticket em status {$current_status} - não é Pendente nem Solucionado - ignorando");
    }
}

/**
 * Hook PÓS-ATUALIZAÇÃO: Executado após salvar a atualização
 * 
 * @param object $item Objeto Ticket que foi atualizado
 * @return void
 */
function plugin_keeppending_item_update($item) {
    if ($item->getType() !== 'Ticket') {
        return;
    }
    
    global $KEEPPENDING_FORCE_PENDING;
    $ticket_id = $item->getID();
    
    // Verificar se este ticket foi marcado para forçar Pendente
    if (isset($KEEPPENDING_FORCE_PENDING[$ticket_id])) {
        $target_status = $KEEPPENDING_FORCE_PENDING[$ticket_id];
        plugin_keeppending_debug_log("=== ITEM_UPDATE (PÓS) Ticket #{$ticket_id} ===");
        plugin_keeppending_debug_log("→ Forçando status para {$target_status} (Pendente)");
        
        // Verificar status atual após a atualização
        global $DB;
        $result = $DB->request([
            'SELECT' => ['status'],
            'FROM'   => 'glpi_tickets',
            'WHERE'  => ['id' => $ticket_id]
        ]);
        
        if ($result->count()) {
            $current = $result->current();
            $current_status = (int)$current['status'];
            plugin_keeppending_debug_log("Status atual após update: {$current_status}");
            
            // Se não está em Pendente, forçar a mudança
            if ($current_status != $target_status) {
                plugin_keeppending_debug_log("→ Status diferente de Pendente! Forçando UPDATE direto no banco");
                
                $DB->update(
                    'glpi_tickets',
                    ['status' => $target_status],
                    ['id' => $ticket_id]
                );
                
                plugin_keeppending_debug_log("✓ Status forçado para {$target_status} via UPDATE direto");
                
                // Registrar no log do GLPI
                Event::log(
                    $ticket_id,
                    'Ticket',
                    4,
                    'keeppending',
                    "Status FORÇADO para PENDENTE após resposta em ticket Solucionado"
                );
            } else {
                plugin_keeppending_debug_log("✓ Status já está em Pendente - OK");
            }
        }
        
        // Limpar o flag
        unset($KEEPPENDING_FORCE_PENDING[$ticket_id]);
    }
}

/**
 * Verifica se é uma mudança MANUAL de status ou AUTOMÁTICA
 * 
 * @param object $item Objeto Ticket
 * @return bool true se é mudança manual, false se é automática
 */
function plugin_keeppending_isManualStatusChange($item) {
    $input = $item->input;
    
    // 1. Se veio do MailCollector, é AUTOMÁTICO
    if (isset($input['_mailgate'])) {
        plugin_keeppending_debug_log("Detectado: _mailgate - mudança via EMAIL");
        return false;
    }
    
    // 2. Se tem flag de email, é AUTOMÁTICO
    if (isset($input['_from_email'])) {
        plugin_keeppending_debug_log("Detectado: _from_email - mudança via EMAIL");
        return false;
    }
    
    // 3. Verificar se é resposta/followup automático
    // Quando um email chega, geralmente não tem HTTP_REFERER de ticket.form.php
    $has_referer = isset($_SERVER['HTTP_REFERER']);
    $referer = $has_referer ? $_SERVER['HTTP_REFERER'] : 'nenhum';
    plugin_keeppending_debug_log("HTTP_REFERER: {$referer}");
    
    if ($has_referer) {
        // Se vem do formulário do ticket via interface web, é MANUAL
        if (strpos($referer, 'ticket.form.php') !== false) {
            plugin_keeppending_debug_log("Referer contém ticket.form.php - MANUAL");
            return true;
        }
    }
    
    // 4. Se tem CSRF token no POST, é interação humana via web
    $has_csrf = isset($_POST['_glpi_csrf_token']) || isset($input['_glpi_csrf_token']);
    plugin_keeppending_debug_log("Tem CSRF token? " . ($has_csrf ? "SIM" : "NÃO"));
    
    if ($has_csrf) {
        // Mas verificar se não é um cron ou mailgate com CSRF
        if (!isset($input['_mailgate']) && !isset($input['_from_email'])) {
            plugin_keeppending_debug_log("CSRF presente sem mailgate - MANUAL");
            return true;
        }
    }
    
    // 5. Se não tem HTTP_REFERER E não tem CSRF, provavelmente é automático (cron, email)
    if (!$has_referer && !$has_csrf) {
        plugin_keeppending_debug_log("Sem REFERER e sem CSRF - AUTOMÁTICO");
        return false;
    }
    
    // 6. Verificar campos típicos de automação
    if (isset($input['content']) && !empty($input['content'])) {
        plugin_keeppending_debug_log("Campo 'content' presente - pode ser automático");
        // Se tem content mas também tem referer de ticket form, é manual
        if ($has_referer && strpos($referer, 'ticket') !== false) {
            return true;
        }
        return false;
    }
    
    // 7. Se usuário está logado e veio de alguma página do GLPI, considerar manual
    if (Session::getLoginUserID() && $has_referer) {
        plugin_keeppending_debug_log("Usuário logado com referer - MANUAL");
        return true;
    }
    
    // Fallback: sem informações suficientes, considerar AUTOMÁTICO para segurança
    plugin_keeppending_debug_log("Fallback - considerando AUTOMÁTICO");
    return false;
}

/**
 * Verifica se o plugin está habilitado
 * 
 * @return bool true se plugin está habilitado
 */
function plugin_keeppending_isEnabled() {
    global $DB;
    
    $table_name = 'glpi_plugin_keeppending_config';
    
    if (!$DB->tableExists($table_name)) {
        plugin_keeppending_debug_log("Tabela {$table_name} não existe - habilitando por padrão");
        return true; // Habilitado por padrão se tabela não existe
    }
    
    $result = $DB->request([
        'SELECT' => ['enable_keep_pending'],
        'FROM'   => $table_name,
        'LIMIT'  => 1
    ]);
    
    if (!$result->count()) {
        plugin_keeppending_debug_log("Tabela vazia - habilitando por padrão");
        return true; // Habilitado por padrão
    }
    
    $config = $result->current();
    $enabled = (bool) $config['enable_keep_pending'];
    plugin_keeppending_debug_log("Config enable_keep_pending: " . ($enabled ? "true" : "false"));
    return $enabled;
}
