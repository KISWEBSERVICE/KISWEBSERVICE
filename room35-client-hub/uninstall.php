<?php
/**
 * Uninstall Room35 Client Hub
 *
 * This file runs when the plugin is uninstalled (deleted).
 * It cleans up all plugin data including database tables, options, and uploaded files.
 *
 * @package Room35_Client_Hub
 */

// Exit if not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if user wants to delete data on uninstall
$settings = get_option('r35_hub_settings', []);
$delete_on_uninstall = isset($settings['delete_on_uninstall']) ? $settings['delete_on_uninstall'] : false;

// Only delete data if explicitly enabled in settings
if (!$delete_on_uninstall) {
    return;
}

global $wpdb;

// Delete database tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}r35_hub_files");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}r35_hub_download_log");

// Delete options
delete_option('r35_hub_settings');
delete_option('r35_hub_db_version');
delete_option('r35_hub_dashboard_page_id');

// Delete the dashboard page
$dashboard_page_id = get_option('r35_hub_dashboard_page_id');
if ($dashboard_page_id) {
    wp_delete_post($dashboard_page_id, true);
}

// Remove client role
remove_role('r35_client');

// Delete uploaded files
$upload_dir = wp_upload_dir();
$plugin_upload_dir = $upload_dir['basedir'] . '/room35-client-files';

if (is_dir($plugin_upload_dir)) {
    r35_hub_recursive_delete($plugin_upload_dir);
}

/**
 * Recursively delete a directory
 *
 * @param string $dir Directory path
 */
function r35_hub_recursive_delete($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($dir . '/' . $object)) {
                    r35_hub_recursive_delete($dir . '/' . $object);
                } else {
                    unlink($dir . '/' . $object);
                }
            }
        }
        rmdir($dir);
    }
}
