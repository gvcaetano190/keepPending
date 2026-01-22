<?php
/**
 * ============================================================================
 * GLPI - Keep Pending Status Plugin - Main Entry Point
 * ============================================================================
 * 
 * This is an alternative entry point for the plugin.
 * Some GLPI versions use this naming convention instead of setup.php
 * 
 * @license     GPL v2 ou superior
 * @link        https://github.com/gvcaetano190/keepPending
 * @author      Gabriel Caetano
 * @version     1.0.0
 * ============================================================================
 */

// Ensure setup.php is loaded
if (file_exists(__DIR__ . '/setup.php')) {
    include_once __DIR__ . '/setup.php';
}

// Ensure init.php is loaded
if (file_exists(__DIR__ . '/init.php')) {
    include_once __DIR__ . '/init.php';
}
