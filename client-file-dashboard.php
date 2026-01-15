<?php
/**
 * Plugin Name: Client File Dashboard
 * Plugin URI: https://example.com/client-file-dashboard
 * Description: A beautiful dashboard for clients to view files provided by site admin, with category organization and user-specific file assignments.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: client-file-dashboard
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('CFD_VERSION', '1.0.0');
define('CFD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CFD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CFD_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_client_file_dashboard() {
    require_once CFD_PLUGIN_DIR . 'includes/class-cfd-activator.php';
    CFD_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_client_file_dashboard() {
    require_once CFD_PLUGIN_DIR . 'includes/class-cfd-activator.php';
    CFD_Activator::deactivate();
}

register_activation_hook(__FILE__, 'activate_client_file_dashboard');
register_deactivation_hook(__FILE__, 'deactivate_client_file_dashboard');

/**
 * The core plugin class.
 */
require CFD_PLUGIN_DIR . 'includes/class-cfd-core.php';

/**
 * Begins execution of the plugin.
 */
function run_client_file_dashboard() {
    $plugin = new CFD_Core();
    $plugin->run();
}
run_client_file_dashboard();
