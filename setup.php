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

// Incluir o init se necessário
if (file_exists(__DIR__ . '/init.php') && basename(dirname(__FILE__)) === 'keeppending') {
    include_once __DIR__ . '/init.php';
}

/**
 * Função de instalação do plugin
 * 
 * @return bool true se instalado com sucesso
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
        
        if ($DB->query($query)) {
            // Inserir configurações padrão
            $DB->insert('glpi_plugin_keeppending_config', [
                'enable_keep_pending' => 1,
                'enable_logs'         => 1
            ]);
        }
    }
    
    return true;
}

/**
 * Função de desinstalação do plugin
 * 
 * @return bool true se desinstalado com sucesso
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
 * Função que retorna informações da versão do plugin
 * 
 * @return array Informações do plugin
 */
function plugin_keeppending_getVersion() {
    return [
        'name'           => 'KeepPending',
        'version'        => '1.0.0',
        'author'         => 'Gabriel Caetano',
        'license'        => 'GPL-2.0',
        'homepage'       => 'https://github.com/gvcaetano190/keepPending',
        'description'    => __('Mantém o status Pendente em chamados quando respostas são adicionadas', 'keeppending'),
        'minGlpiVersion' => '10.0.0',
        'maxGlpiVersion' => '10.9.9',
    ];
}


