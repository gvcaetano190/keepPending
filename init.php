<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Initialization
 * ============================================================================
 * 
 * Arquivo de inicialização do plugin KeepPending para GLPI
 * Responsável por registrar os hooks do plugin
 * 
 * @license     GPL v2 ou superior
 * @link        https://github.com/gvcaetano190/keepPending
 * @author      Gabriel Caetano
 * @version     1.0.0
 * ============================================================================
 */

/**
 * Incluir o arquivo de hooks
 */
if (file_exists(__DIR__ . '/hook.php')) {
    include_once __DIR__ . '/hook.php';
}

/**
 * Incluir classes do plugin
 */
if (file_exists(__DIR__ . '/inc/Config.class.php')) {
    include_once __DIR__ . '/inc/Config.class.php';
}

/**
 * Função chamada após a inicialização do GLPI
 * Registra os hooks do plugin
 * 
 * @return void
 */
function plugin_keeppending_postInit() {
    global $PLUGIN_HOOKS;
    
    if (!isset($PLUGIN_HOOKS['pre_item_update'])) {
        $PLUGIN_HOOKS['pre_item_update'] = [];
    }
    if (!isset($PLUGIN_HOOKS['item_update'])) {
        $PLUGIN_HOOKS['item_update'] = [];
    }
    
    // Hook para interceptar atualização de tickets
    $PLUGIN_HOOKS['pre_item_update']['keeppending'] = 'plugin_keeppending_pre_item_update';
    
    // Hook para registrar logs (opcional)
    $PLUGIN_HOOKS['item_update']['keeppending'] = 'plugin_keeppending_item_update';
}

/**
 * Hook de visualização de item (para frontend)
 */
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'front') !== false) {
    global $PLUGIN_HOOKS;
    
    if (!isset($PLUGIN_HOOKS['display_central'])) {
        $PLUGIN_HOOKS['display_central'] = [];
    }
}
