<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Config Class
 * ============================================================================
 *
 * Classe de configuração compatível com GLPI 10 e 11.
 *
 * ============================================================================
 */

class PluginKeeppendingConfig extends CommonDBTM {

    public static $rightname = 'config';

    public static function getTypeName($nb = 0) {
        return __('Configurações - KeepPending', 'keeppending');
    }

    public function getName($with_comment = 0) {
        return __('KeepPending', 'keeppending');
    }

    /**
     * Escape seguro para GLPI 10 e 11.
     *
     * @param mixed $value
     * @return string
     */
    private static function escape($value) {
        if (function_exists('htmlescape')) {
            return htmlescape((string) $value);
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Configuração padrão do plugin.
     *
     * @return array
     */
    public static function getDefaultConfig() {
        return [
            'enable_keep_pending' => 1,
            'enable_keep_solved'  => 1,
            'enable_logs'         => 1,
        ];
    }

    /**
     * Obtém as configurações atuais do plugin.
     * Prioriza o storage nativo do GLPI e mantém fallback para a tabela legada.
     *
     * @return array
     */
    public static function getConfig() {
        $config = self::getDefaultConfig();

        if (class_exists('Config') && method_exists('Config', 'getConfigurationValues')) {
            $stored = Config::getConfigurationValues('plugin:keeppending');
            if (is_array($stored) && count($stored)) {
                return [
                    'enable_keep_pending' => (int) ($stored['enable_keep_pending'] ?? $config['enable_keep_pending']),
                    'enable_keep_solved'  => (int) ($stored['enable_keep_solved'] ?? $config['enable_keep_solved']),
                    'enable_logs'         => (int) ($stored['enable_logs'] ?? $config['enable_logs']),
                ];
            }
        }

        global $DB;
        if (isset($DB) && $DB->tableExists('glpi_plugin_keeppending_config')) {
            $result = $DB->request([
                'SELECT' => '*',
                'FROM'   => 'glpi_plugin_keeppending_config',
                'LIMIT'  => 1,
            ]);

            if ($result->count()) {
                $data = $result->current();
                return [
                    'enable_keep_pending' => (int) ($data['enable_keep_pending'] ?? 1),
                    'enable_keep_solved'  => (int) ($data['enable_keep_solved'] ?? 1),
                    'enable_logs'         => (int) ($data['enable_logs'] ?? 1),
                ];
            }
        }

        return $config;
    }

    /**
     * Salva as configurações em storage nativo do GLPI e sincroniza tabela legada.
     *
     * @param array $data
     * @return bool
     */
    public static function saveConfig(array $data) {
        global $DB;

        $config = [
            'enable_keep_pending' => !empty($data['enable_keep_pending']) ? 1 : 0,
            'enable_keep_solved'  => !empty($data['enable_keep_solved']) ? 1 : 0,
            'enable_logs'         => !empty($data['enable_logs']) ? 1 : 0,
        ];

        if (class_exists('Config') && method_exists('Config', 'setConfigurationValues')) {
            Config::setConfigurationValues('plugin:keeppending', $config);
        }

        if (isset($DB) && $DB->tableExists('glpi_plugin_keeppending_config')) {
            $result = $DB->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_plugin_keeppending_config',
                'LIMIT'  => 1,
            ]);

            if ($result->count()) {
                $row = $result->current();
                $DB->update('glpi_plugin_keeppending_config', $config, ['id' => $row['id']]);
            } else {
                $DB->insert('glpi_plugin_keeppending_config', $config);
            }
        }

        return true;
    }

    /**
     * Migra configurações da tabela legada para o storage de configuração do GLPI.
     *
     * @return bool
     */
    public static function migrateLegacyConfig() {
        $config = self::getDefaultConfig();

        global $DB;
        if (isset($DB) && $DB->tableExists('glpi_plugin_keeppending_config')) {
            $result = $DB->request([
                'SELECT' => '*',
                'FROM'   => 'glpi_plugin_keeppending_config',
                'LIMIT'  => 1,
            ]);

            if ($result->count()) {
                $data = $result->current();
                $config = [
                    'enable_keep_pending' => (int) ($data['enable_keep_pending'] ?? 1),
                    'enable_keep_solved'  => (int) ($data['enable_keep_solved'] ?? 1),
                    'enable_logs'         => (int) ($data['enable_logs'] ?? 1),
                ];
            }
        }

        if (class_exists('Config') && method_exists('Config', 'setConfigurationValues')) {
            Config::setConfigurationValues('plugin:keeppending', $config);
        }

        return true;
    }

    /**
     * Exibe o formulário de configuração do plugin.
     *
     * @param mixed $item
     * @return bool
     */
    public static function showConfigForm($item) {
        global $CFG_GLPI;

        $config   = self::getConfig();
        $root_doc = is_array($CFG_GLPI ?? null) ? ($CFG_GLPI['root_doc'] ?? '') : '';
        $action   = $root_doc . '/plugins/keeppending/front/config.form.php';

        echo "<form name='form' action=\"" . self::escape($action) . "\" method='post' data-track-changes='true'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo "<div class='center' id='tabsbody'>";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr><th colspan='2'>" . self::escape(__('KeepPending - Configurações', 'keeppending')) . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . self::escape(__('Proteger Status Pendente (4)', 'keeppending')) . "</td>";
        echo "<td>";
        Dropdown::showYesNo('enable_keep_pending', (int) $config['enable_keep_pending']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . self::escape(__('Proteger Status Solucionado (5)', 'keeppending')) . "</td>";
        echo "<td>";
        Dropdown::showYesNo('enable_keep_solved', (int) $config['enable_keep_solved']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . self::escape(__('Habilitar Logs', 'keeppending')) . "</td>";
        echo "<td>";
        Dropdown::showYesNo('enable_logs', (int) $config['enable_logs']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>" . self::escape(__('Controla mudanças automáticas de status em tickets Pendente e Solucionado.', 'keeppending')) . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='2' class='center'>";
        echo "<input type='submit' name='update' class='submit' value=\"" . self::escape(_sx('button', 'Save')) . "\">";
        echo "</td></tr>";

        echo "</table></div>";
        Html::closeForm();

        return false;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() === 'Config') {
            return __('KeepPending', 'keeppending');
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() === 'Config') {
            self::showConfigForm($item);
        }

        return true;
    }

    /**
     * Retorna informações sobre os status protegidos.
     *
     * @return array
     */
    public static function getProtectedStatusInfo() {
        return [
            'pending' => [
                'status_id'   => 4,
                'status_name' => 'pending',
                'description' => __('Status Pendente - Aguardando resposta do solicitante', 'keeppending')
            ],
            'solved' => [
                'status_id'   => 5,
                'status_name' => 'solved',
                'description' => __('Status Solucionado - Aguardando confirmação do solicitante', 'keeppending')
            ]
        ];
    }
}

if (!class_exists('GlpiPlugin\\Keeppending\\Config')) {
    class_alias('PluginKeeppendingConfig', 'GlpiPlugin\\Keeppending\\Config');
}
