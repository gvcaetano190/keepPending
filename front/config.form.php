<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Config Form Redirect
 * ============================================================================
 *
 * Ponto de entrada compatível com GLPI 10 e 11 para a tela de configuração.
 *
 * ============================================================================
 */

if (!defined('GLPI_ROOT')) {
    include('../../../inc/includes.php');
}

Session::checkRight('config', READ);

if (isset($_POST['update'])) {
    Session::checkRight('config', UPDATE);
    Html::checkCSRF();

    PluginKeeppendingConfig::saveConfig($_POST);

    Session::addMessageAfterRedirect(
        __('Configurações salvas com sucesso!', 'keeppending'),
        true,
        INFO
    );
}

Session::setActiveTab('Config', 'PluginKeeppendingConfig$1');
Html::redirect($CFG_GLPI['root_doc'] . '/front/config.form.php');
