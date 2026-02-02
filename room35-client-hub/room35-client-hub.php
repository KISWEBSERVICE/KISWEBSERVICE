<?php
/**
 * Plugin Name:       Room35 Client Hub
 * Plugin URI:        https://roomthirtyfive.com/plugins/client-hub
 * Description:       Securely share files between admins and clients with a modern dashboard. Allows admins to upload files for specific clients or all clients, and clients can upload files back.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Room Thirty Five
 * Author URI:        https://roomthirtyfive.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       room35-client-hub
 * Domain Path:       /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('R35_HUB_VERSION', '1.0.0');
define('R35_HUB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('R35_HUB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('R35_HUB_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('R35_HUB_UPLOAD_DIR', 'room35-client-files');
define('R35_HUB_ADMIN_EMAIL', 'joshua@roomthirtyfive.com');

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'R35_Hub_';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $class_name = str_replace($prefix, '', $class);
    $class_name = strtolower(str_replace('_', '-', $class_name));

    $paths = [
        R35_HUB_PLUGIN_DIR . 'includes/class-r35-hub-' . $class_name . '.php',
        R35_HUB_PLUGIN_DIR . 'admin/class-r35-hub-' . $class_name . '.php',
        R35_HUB_PLUGIN_DIR . 'public/class-r35-hub-' . $class_name . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Manual includes for core files
require_once R35_HUB_PLUGIN_DIR . 'includes/class-r35-hub-activator.php';
require_once R35_HUB_PLUGIN_DIR . 'includes/class-r35-hub-deactivator.php';
require_once R35_HUB_PLUGIN_DIR . 'includes/class-r35-hub-security.php';
require_once R35_HUB_PLUGIN_DIR . 'includes/class-r35-hub-user-role.php';
require_once R35_HUB_PLUGIN_DIR . 'includes/class-r35-hub-file-handler.php';
require_once R35_HUB_PLUGIN_DIR . 'includes/class-r35-hub-email-notifications.php';

// Admin includes
if (is_admin()) {
    require_once R35_HUB_PLUGIN_DIR . 'admin/class-r35-hub-admin.php';
    require_once R35_HUB_PLUGIN_DIR . 'admin/class-r35-hub-admin-file-list.php';
}

// Public includes
require_once R35_HUB_PLUGIN_DIR . 'public/class-r35-hub-shortcode.php';

/**
 * Plugin activation hook
 */
function r35_hub_activate() {
    R35_Hub_Activator::activate();
}
register_activation_hook(__FILE__, 'r35_hub_activate');

/**
 * Plugin deactivation hook
 */
function r35_hub_deactivate() {
    R35_Hub_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'r35_hub_deactivate');

/**
 * Initialize the plugin
 */
function r35_hub_init() {
    // Load text domain
    load_plugin_textdomain('room35-client-hub', false, dirname(R35_HUB_PLUGIN_BASENAME) . '/languages');

    // Initialize user role management
    R35_Hub_User_Role::init();

    // Initialize admin
    if (is_admin()) {
        $admin = new R35_Hub_Admin();
        $admin->init();
    }

    // Initialize shortcode
    $shortcode = new R35_Hub_Shortcode();
    $shortcode->init();

    // Initialize secure file download endpoint
    r35_hub_init_download_endpoint();
}
add_action('plugins_loaded', 'r35_hub_init');

/**
 * Initialize secure download endpoint
 */
function r35_hub_init_download_endpoint() {
    add_action('init', function() {
        add_rewrite_endpoint('r35-download', EP_ROOT);
    });

    add_action('template_redirect', function() {
        $download_param = get_query_var('r35-download');
        if ($download_param) {
            $file_handler = new R35_Hub_File_Handler();
            $file_handler->process_download_request($download_param);
            exit;
        }
    });
}

/**
 * Add query vars
 */
function r35_hub_query_vars($vars) {
    $vars[] = 'r35-download';
    $vars[] = 'r35-view';
    return $vars;
}
add_filter('query_vars', 'r35_hub_query_vars');

/**
 * AJAX handlers
 */
// Admin upload
add_action('wp_ajax_r35_admin_upload', function() {
    check_ajax_referer('r35_hub_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Unauthorized access.', 'room35-client-hub')], 403);
    }

    $file_handler = new R35_Hub_File_Handler();
    $assigned_to = isset($_POST['assigned_to']) ? absint($_POST['assigned_to']) : 0;
    $is_global = isset($_POST['is_global']) && $_POST['is_global'] === '1';

    $result = $file_handler->upload_file($_FILES['file'], $assigned_to, $is_global, 'admin');

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    // Send notification email
    if (!$is_global && $assigned_to > 0) {
        $email = new R35_Hub_Email_Notifications();
        $email->notify_client_new_file($result, $assigned_to);
    }

    wp_send_json_success([
        'message' => __('File uploaded successfully.', 'room35-client-hub'),
        'file_id' => $result
    ]);
});

// Admin delete
add_action('wp_ajax_r35_admin_delete', function() {
    check_ajax_referer('r35_hub_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Unauthorized access.', 'room35-client-hub')], 403);
    }

    $file_id = isset($_POST['file_id']) ? absint($_POST['file_id']) : 0;

    if (!$file_id) {
        wp_send_json_error(['message' => __('Invalid file ID.', 'room35-client-hub')]);
    }

    $file_handler = new R35_Hub_File_Handler();
    $result = $file_handler->delete_file($file_id);

    if (!$result) {
        wp_send_json_error(['message' => __('Failed to delete file.', 'room35-client-hub')]);
    }

    wp_send_json_success(['message' => __('File deleted successfully.', 'room35-client-hub')]);
});

// Client upload
add_action('wp_ajax_r35_client_upload', function() {
    check_ajax_referer('r35_hub_client_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('You must be logged in to upload files.', 'room35-client-hub')], 403);
    }

    $user_id = get_current_user_id();
    $file_handler = new R35_Hub_File_Handler();

    $result = $file_handler->upload_file($_FILES['file'], $user_id, false, 'client');

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    // Send notification email to admin
    $email = new R35_Hub_Email_Notifications();
    $email->notify_admin_new_file($result, $user_id);

    wp_send_json_success([
        'message' => __('File uploaded successfully.', 'room35-client-hub'),
        'file_id' => $result
    ]);
});

// Get files for admin table
add_action('wp_ajax_r35_get_files', function() {
    check_ajax_referer('r35_hub_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Unauthorized access.', 'room35-client-hub')], 403);
    }

    $file_handler = new R35_Hub_File_Handler();
    $filter_user = isset($_GET['filter_user']) ? absint($_GET['filter_user']) : 0;
    $filter_source = isset($_GET['filter_source']) ? sanitize_text_field($_GET['filter_source']) : '';

    $args = [];
    if ($filter_user > 0) {
        $args['assigned_to'] = $filter_user;
    }
    if ($filter_source) {
        $args['upload_source'] = $filter_source;
    }

    $files = $file_handler->get_all_files($args);

    wp_send_json_success(['files' => $files]);
});
