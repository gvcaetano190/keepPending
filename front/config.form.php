<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Config Form
 * ============================================================================
 * 
 * @license     GPL v2 ou superior
 * @link        https://github.com/gvcaetano190/keepPending
 * @author      Gabriel Caetano
 * @version     1.0.0
 * ============================================================================
 */

include('../../../inc/includes.php');

Session::checkRight('config', READ);

// Processar formulário se enviado
if (isset($_POST['update'])) {
    Session::checkRight('config', UPDATE);
    
    global $DB;
    
    if ($DB->tableExists('glpi_plugin_keeppending_config')) {
        $update_data = [
            'enable_keep_pending' => isset($_POST['enable_keep_pending']) ? 1 : 0,
            'enable_keep_solved'  => isset($_POST['enable_keep_solved']) ? 1 : 0,
            'enable_logs'         => isset($_POST['enable_logs']) ? 1 : 0
        ];
        
        $DB->update(
            'glpi_plugin_keeppending_config',
            $update_data,
            ['id' => 1]
        );
        
        Session::addMessageAfterRedirect(
            __('Configurações salvas com sucesso!', 'keeppending'),
            true,
            INFO
        );
    }
    
    Html::redirect($_SERVER['PHP_SELF']);
}

// Obter configurações atuais
global $DB;
$config = [
    'enable_keep_pending' => true,
    'enable_keep_solved'  => true,
    'enable_logs'         => true
];

if ($DB->tableExists('glpi_plugin_keeppending_config')) {
    $result = $DB->request([
        'SELECT' => '*',
        'FROM'   => 'glpi_plugin_keeppending_config',
        'LIMIT'  => 1
    ]);
    
    if ($result->count()) {
        $data = $result->current();
        $config = [
            'enable_keep_pending' => (bool) $data['enable_keep_pending'],
            'enable_keep_solved'  => (bool) ($data['enable_keep_solved'] ?? true),
            'enable_logs'         => (bool) $data['enable_logs']
        ];
    }
}

Html::header(
    __('KeepPending', 'keeppending'),
    $_SERVER['PHP_SELF'],
    'config',
    'plugins'
);

echo "<div class='center'>";
echo "<h2>" . __('KeepPending - Configurações', 'keeppending') . "</h2>";
echo "</div>";

// Formulário de configurações
echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);

echo "<div class='center' style='margin-top: 20px;'>";
echo "<table class='tab_cadre_fixe'>";

echo "<tr class='tab_bg_1'>";
echo "<th colspan='2'>" . __('Status Protegidos', 'keeppending') . "</th>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Proteger Status Pendente (4)', 'keeppending') . "</td>";
echo "<td>";
echo "<input type='checkbox' name='enable_keep_pending' value='1' " . ($config['enable_keep_pending'] ? 'checked' : '') . ">";
echo " <small>" . __('Impede mudança automática quando ticket está Pendente', 'keeppending') . "</small>";
echo "</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Proteger Status Solucionado (5)', 'keeppending') . "</td>";
echo "<td>";
echo "<input type='checkbox' name='enable_keep_solved' value='1' " . ($config['enable_keep_solved'] ? 'checked' : '') . ">";
echo " <small>" . __('Impede mudança automática quando ticket está Solucionado', 'keeppending') . "</small>";
echo "</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<th colspan='2'>" . __('Outras Opções', 'keeppending') . "</th>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Habilitar Logs', 'keeppending') . "</td>";
echo "<td>";
echo "<input type='checkbox' name='enable_logs' value='1' " . ($config['enable_logs'] ? 'checked' : '') . ">";
echo " <small>" . __('Registra ações do plugin em /files/_log/keeppending.log', 'keeppending') . "</small>";
echo "</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td colspan='2' class='center'>";
echo "<input type='submit' name='update' value='" . __('Salvar', 'keeppending') . "' class='btn btn-primary'>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</div>";

echo Html::closeForm(false);

// Informações do plugin
echo "<div class='center' style='margin-top: 30px;'>";
echo "<table class='tab_cadre_fixe'>";

echo "<tr class='tab_bg_1'>";
echo "<th colspan='2'>" . __('Informações do Plugin', 'keeppending') . "</th>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Versão', 'keeppending') . "</td>";
echo "<td><strong>" . PLUGIN_KEEPPENDING_VERSION . "</strong></td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Autor', 'keeppending') . "</td>";
echo "<td>Gabriel Caetano</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<th colspan='2'>" . __('Descrição', 'keeppending') . "</th>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td colspan='2'>";
echo "<p>" . __('Este plugin controla mudanças automáticas de status quando respostas são adicionadas.', 'keeppending') . "</p>";
echo "<p><strong>" . __('Comportamento:', 'keeppending') . "</strong></p>";
echo "<ul style='text-align: left; margin-left: 40px;'>";
echo "<li>✅ " . __('PERMITE mudanças manuais de status', 'keeppending') . "</li>";
echo "<li>🔄 " . __('REDIRECIONA mudanças automáticas', 'keeppending') . "</li>";
echo "</ul>";
echo "<p><strong>" . __('Cenários protegidos:', 'keeppending') . "</strong></p>";
echo "<ul style='text-align: left; margin-left: 40px;'>";
echo "<li>" . __('Pendente: Cliente responde → Status MANTÉM "Pendente"', 'keeppending') . "</li>";
echo "<li>" . __('Solucionado: Cliente responde → Status MUDA para "Pendente" (não "Em atendimento")', 'keeppending') . "</li>";
echo "</ul>";
echo "<p><em>" . __('Isso protege seu SLA quando clientes respondem após solução.', 'keeppending') . "</em></p>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</div>";

echo "<div class='center' style='margin-top: 20px;'>";
echo "<a href='https://github.com/gvcaetano190/keepPending' target='_blank' class='btn btn-primary'>";
echo __('Documentação no GitHub', 'keeppending');
echo "</a>";
echo "</div>";

Html::footer();
