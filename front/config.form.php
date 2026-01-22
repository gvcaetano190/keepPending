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

Html::header(
    __('KeepPending', 'keeppending'),
    $_SERVER['PHP_SELF'],
    'config',
    'plugins'
);

echo "<div class='center'>";
echo "<h2>" . __('KeepPending - Configurações', 'keeppending') . "</h2>";
echo "</div>";

echo "<div class='center' style='margin-top: 20px;'>";
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
echo "<td>" . __('Licença', 'keeppending') . "</td>";
echo "<td>GPLv2+</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<th colspan='2'>" . __('Descrição', 'keeppending') . "</th>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td colspan='2'>";
echo "<p>" . __('Este plugin mantém o status "Pendente" em chamados quando respostas são adicionadas automaticamente.', 'keeppending') . "</p>";
echo "<p><strong>" . __('Comportamento:', 'keeppending') . "</strong></p>";
echo "<ul style='text-align: left; margin-left: 40px;'>";
echo "<li>✅ " . __('PERMITE mudanças manuais de status', 'keeppending') . "</li>";
echo "<li>❌ " . __('BLOQUEIA mudanças automáticas (respostas, emails)', 'keeppending') . "</li>";
echo "</ul>";
echo "</td>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<th colspan='2'>" . __('Status', 'keeppending') . "</th>";
echo "</tr>";

echo "<tr class='tab_bg_1'>";
echo "<td>" . __('Plugin Ativo', 'keeppending') . "</td>";
echo "<td><span style='color: green; font-weight: bold;'>✓ " . __('Funcionando', 'keeppending') . "</span></td>";
echo "</tr>";

echo "</table>";
echo "</div>";

echo "<div class='center' style='margin-top: 20px;'>";
echo "<a href='https://github.com/gvcaetano190/keepPending' target='_blank' class='btn btn-primary'>";
echo __('Documentação no GitHub', 'keeppending');
echo "</a>";
echo "</div>";

Html::footer();
