<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Config Class
 * ============================================================================
 * 
 * Classe para gerenciar configurações do plugin
 * 
 * ============================================================================
 */

namespace GlpiPlugin\Keeppending;

class Config extends \CommonDBTM {
    
    public static $rightname = 'config';
    
    public static function getTypeName($nb = 0) {
        return __('Configurações - Keeppending', 'keepPending');
    }
    
    /**
     * Obtém as configurações atuais do plugin
     * 
     * @return array Array com as configurações
     */
    public static function getConfig() {
        global $DB;
        
        $config = [
            'enable_keep_pending' => true,
            'enable_logs'         => true
        ];
        
        if (!$DB->tableExists('glpi_plugin_keeppending_config')) {
            return $config;
        }
        
        $result = $DB->request([
            'SELECT' => '*',
            'FROM'   => 'glpi_plugin_keeppending_config',
            'LIMIT'  => 1
        ]);
        
        if ($result->count()) {
            $data = $result->current();
            return [
                'enable_keep_pending' => (bool) $data['enable_keep_pending'],
                'enable_logs'         => (bool) $data['enable_logs']
            ];
        }
        
        return $config;
    }
    
    /**
     * Atualiza as configurações do plugin
     * 
     * @param array $data Array com as novas configurações
     * @return bool true se atualizado com sucesso
     */
    public static function updateConfig($data) {
        global $DB;
        
        if (!$DB->tableExists('glpi_plugin_keeppending_config')) {
            return false;
        }
        
        $update_data = [
            'enable_keep_pending' => isset($data['enable_keep_pending']) ? 1 : 0,
            'enable_logs'         => isset($data['enable_logs']) ? 1 : 0
        ];
        
        return $DB->update(
            'glpi_plugin_keeppending_config',
            $update_data,
            ['id' => 1]
        );
    }
    
    /**
     * Retorna informações sobre o status de pendência
     * 
     * @return array Informações sobre status pendente
     */
    public static function getPendingStatusInfo() {
        return [
            'status_id'   => 5,
            'status_name' => 'pending',
            'description' => __('Status Pendente - Aguardando ação do usuário', 'keepPending')
        ];
    }
}
