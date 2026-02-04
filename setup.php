<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin
 * ============================================================================
 * 
 * Plugin para manter o status "Pendente" em chamados quando respostas são
 * adicionadas, evitando que tickets passem automaticamente para outro status.
 * 
 * @license     GPL v2 ou superior
 * @link        https://github.com/gvcaetano190/keepPending
 * @author      Gabriel Caetano
 * @version     1.0.0
 * ============================================================================
 */

define('PLUGIN_KEEPPENDING_VERSION', '1.0.0');

/**
 * Init the hooks of the plugins - Needed
 * 
 * @return void
 */
function plugin_init_keeppending() {
    global $PLUGIN_HOOKS;
    
    // CSRF compliance - OBRIGATÓRIO para GLPI 10+
    $PLUGIN_HOOKS['csrf_compliant']['keeppending'] = true;
    
    // Página de configuração do plugin
    $PLUGIN_HOOKS['config_page']['keeppending'] = 'front/config.form.php';
    
    // Hook para interceptar atualização de tickets (PRE - antes de salvar)
    // IMPORTANTE: Deve ser um array com o tipo de item como chave
    $PLUGIN_HOOKS['pre_item_update']['keeppending'] = [
        'Ticket' => 'plugin_keeppending_pre_item_update'
    ];
    
    // Hook para registrar logs (POST - depois de salvar)
    $PLUGIN_HOOKS['item_update']['keeppending'] = [
        'Ticket' => 'plugin_keeppending_item_update'
    ];
}

/**
 * Get the name and the version of the plugin - Needed
 * 
 * @return array
 */
function plugin_version_keeppending() {
    return [
        'name'           => 'KeepPending',
        'version'        => PLUGIN_KEEPPENDING_VERSION,
        'author'         => 'Gabriel Caetano',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://github.com/gvcaetano190/keepPending',
        'requirements'   => [
            'glpi' => [
                'min' => '10.0.0',
                'max' => '10.9.99',
            ],
            'php' => [
                'min' => '8.0',
            ]
        ]
    ];
}

/**
 * Optional: check prerequisites before install
 * 
 * @return boolean
 */
function plugin_keeppending_check_prerequisites() {
    // Verificar versão mínima do GLPI
    if (version_compare(GLPI_VERSION, '10.0.0', 'lt')) {
        echo "Este plugin requer GLPI >= 10.0.0";
        return false;
    }
    return true;
}

/**
 * Check configuration process for plugin
 * 
 * @param boolean $verbose Enable verbosity
 * @return boolean
 */
function plugin_keeppending_check_config($verbose = false) {
    return true;
}