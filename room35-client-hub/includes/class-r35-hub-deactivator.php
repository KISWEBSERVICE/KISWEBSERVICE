<?php
/**
 * Plugin Deactivator
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_Deactivator {

    /**
     * Run deactivation tasks
     * Note: We don't delete data on deactivation, only on uninstall
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
