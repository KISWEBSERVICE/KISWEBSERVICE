<?php
/**
 * Admin functionality
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_Admin {

    /**
     * Initialize admin hooks
     */
    public function init() {
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Register admin menus
     */
    public function register_menus() {
        // Main menu
        add_menu_page(
            __('Client Hub', 'room35-client-hub'),
            __('Client Hub', 'room35-client-hub'),
            'manage_options',
            'r35-hub-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-portfolio',
            26
        );

        // Dashboard submenu
        add_submenu_page(
            'r35-hub-dashboard',
            __('Dashboard', 'room35-client-hub'),
            __('Dashboard', 'room35-client-hub'),
            'manage_options',
            'r35-hub-dashboard',
            [$this, 'render_dashboard']
        );

        // All Files
        add_submenu_page(
            'r35-hub-dashboard',
            __('All Files', 'room35-client-hub'),
            __('All Files', 'room35-client-hub'),
            'manage_options',
            'r35-hub-files',
            [$this, 'render_files']
        );

        // Upload File
        add_submenu_page(
            'r35-hub-dashboard',
            __('Upload File', 'room35-client-hub'),
            __('Upload File', 'room35-client-hub'),
            'manage_options',
            'r35-hub-upload',
            [$this, 'render_upload']
        );

        // Client Files
        add_submenu_page(
            'r35-hub-dashboard',
            __('Client Files', 'room35-client-hub'),
            __('Client Files', 'room35-client-hub'),
            'manage_options',
            'r35-hub-client-files',
            [$this, 'render_client_files']
        );

        // Settings
        add_submenu_page(
            'r35-hub-dashboard',
            __('Settings', 'room35-client-hub'),
            __('Settings', 'room35-client-hub'),
            'manage_options',
            'r35-hub-settings',
            [$this, 'render_settings']
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'r35-hub') === false) {
            return;
        }

        wp_enqueue_style(
            'r35-hub-admin',
            R35_HUB_PLUGIN_URL . 'admin/css/r35-hub-admin.css',
            [],
            R35_HUB_VERSION
        );

        wp_enqueue_script(
            'r35-hub-admin',
            R35_HUB_PLUGIN_URL . 'admin/js/r35-hub-admin.js',
            ['jquery'],
            R35_HUB_VERSION,
            true
        );

        wp_localize_script('r35-hub-admin', 'r35HubAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('r35_hub_nonce'),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this file? This cannot be undone.', 'room35-client-hub'),
                'uploading' => __('Uploading...', 'room35-client-hub'),
                'uploadSuccess' => __('File uploaded successfully!', 'room35-client-hub'),
                'uploadError' => __('Upload failed. Please try again.', 'room35-client-hub'),
                'deleteSuccess' => __('File deleted successfully!', 'room35-client-hub'),
                'deleteError' => __('Failed to delete file.', 'room35-client-hub'),
            ]
        ]);
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        $file_handler = new R35_Hub_File_Handler();

        // Get stats
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        $total_files = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'active'");
        $admin_files = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'active' AND upload_source = 'admin'");
        $client_files = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'active' AND upload_source = 'client'");
        $global_files = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'active' AND is_global = 1");
        $total_clients = count(R35_Hub_User_Role::get_all_clients());

        // Recent files
        $recent_files = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE status = 'active' ORDER BY created_at DESC LIMIT 10"
        );

        include R35_HUB_PLUGIN_DIR . 'admin/partials/admin-dashboard.php';
    }

    /**
     * Render all files page
     */
    public function render_files() {
        $list_table = new R35_Hub_Admin_File_List();
        $list_table->prepare_items();

        include R35_HUB_PLUGIN_DIR . 'admin/partials/admin-file-list.php';
    }

    /**
     * Render upload page
     */
    public function render_upload() {
        $clients = R35_Hub_User_Role::get_assignable_users();
        include R35_HUB_PLUGIN_DIR . 'admin/partials/admin-upload-form.php';
    }

    /**
     * Render client files page
     */
    public function render_client_files() {
        $clients = R35_Hub_User_Role::get_assignable_users();
        $file_handler = new R35_Hub_File_Handler();

        // Get selected client
        $selected_client = isset($_GET['client_id']) ? absint($_GET['client_id']) : 0;

        if ($selected_client) {
            $client_files = $file_handler->get_files_for_user($selected_client);
            $client_uploads = $file_handler->get_client_uploads($selected_client);
            $client = get_userdata($selected_client);
        } else {
            $client_files = [];
            $client_uploads = [];
            $client = null;
        }

        include R35_HUB_PLUGIN_DIR . 'admin/partials/admin-client-files.php';
    }

    /**
     * Render settings page
     */
    public function render_settings() {
        // Handle form submission
        if (isset($_POST['r35_hub_save_settings']) && check_admin_referer('r35_hub_settings_nonce')) {
            $settings = [
                'max_file_size' => absint($_POST['max_file_size']),
                'email_notifications' => isset($_POST['email_notifications']) ? true : false,
                'admin_email' => sanitize_email($_POST['admin_email']),
                'email_from_name' => sanitize_text_field($_POST['email_from_name']),
                'allowed_extensions' => array_map('sanitize_text_field', explode(',', $_POST['allowed_extensions'])),
            ];

            update_option('r35_hub_settings', $settings);
            $saved = true;
        }

        $settings = get_option('r35_hub_settings', []);
        $dashboard_page_id = get_option('r35_hub_dashboard_page_id');

        include R35_HUB_PLUGIN_DIR . 'admin/partials/admin-settings.php';
    }
}
