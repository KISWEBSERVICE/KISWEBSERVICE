<?php
/**
 * Plugin Activator
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_Activator {

    /**
     * Run activation tasks
     */
    public static function activate() {
        self::create_tables();
        self::create_upload_directory();
        self::create_client_role();
        self::create_dashboard_page();
        self::set_default_options();

        // Flush rewrite rules for download endpoint
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Files table
        $table_files = $wpdb->prefix . 'r35_hub_files';
        $sql_files = "CREATE TABLE {$table_files} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            file_name VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            file_size BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            file_hash VARCHAR(64) DEFAULT NULL,
            uploaded_by BIGINT(20) UNSIGNED NOT NULL,
            assigned_to BIGINT(20) UNSIGNED DEFAULT NULL,
            is_global TINYINT(1) NOT NULL DEFAULT 0,
            upload_source ENUM('admin', 'client') NOT NULL DEFAULT 'admin',
            status ENUM('active', 'deleted') NOT NULL DEFAULT 'active',
            download_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_assigned_to (assigned_to),
            KEY idx_uploaded_by (uploaded_by),
            KEY idx_is_global (is_global),
            KEY idx_status (status),
            KEY idx_created_at (created_at),
            KEY idx_upload_source (upload_source)
        ) {$charset_collate};";

        // Download log table
        $table_logs = $wpdb->prefix . 'r35_hub_download_log';
        $sql_logs = "CREATE TABLE {$table_logs} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            file_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(500) DEFAULT NULL,
            downloaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_file_id (file_id),
            KEY idx_user_id (user_id),
            KEY idx_downloaded_at (downloaded_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_files);
        dbDelta($sql_logs);

        update_option('r35_hub_db_version', '1.0.0');
    }

    /**
     * Create upload directory with security
     */
    private static function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/' . R35_HUB_UPLOAD_DIR;

        // Create main directory
        if (!file_exists($base_path)) {
            wp_mkdir_p($base_path);
        }

        // Create .htaccess to deny direct access
        $htaccess_content = "# Room35 Client Hub - Deny direct access\n";
        $htaccess_content .= "Order deny,allow\n";
        $htaccess_content .= "Deny from all\n\n";
        $htaccess_content .= "<FilesMatch \".*\">\n";
        $htaccess_content .= "    Order Allow,Deny\n";
        $htaccess_content .= "    Deny from all\n";
        $htaccess_content .= "</FilesMatch>\n";

        file_put_contents($base_path . '/.htaccess', $htaccess_content);

        // Create index.php for extra security
        file_put_contents($base_path . '/index.php', '<?php // Silence is golden');
    }

    /**
     * Create client role
     */
    private static function create_client_role() {
        // Remove first to ensure clean state
        remove_role('r35_client');

        // Add the client role
        add_role(
            'r35_client',
            __('Client', 'room35-client-hub'),
            [
                'read' => true,
                'upload_files' => false, // We handle uploads through our own system
            ]
        );
    }

    /**
     * Create the dashboard page automatically
     */
    private static function create_dashboard_page() {
        // Check if page already exists
        $existing_page_id = get_option('r35_hub_dashboard_page_id');
        if ($existing_page_id && get_post($existing_page_id)) {
            return;
        }

        // Create the dashboard page
        $page_data = [
            'post_title'     => __('Client Dashboard', 'room35-client-hub'),
            'post_content'   => '[room35_client_hub]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ];

        $page_id = wp_insert_post($page_data);

        if ($page_id && !is_wp_error($page_id)) {
            update_option('r35_hub_dashboard_page_id', $page_id);
        }
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $defaults = [
            'max_file_size'       => 24,
            'allowed_extensions'  => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
            'email_notifications' => true,
            'admin_email'         => R35_HUB_ADMIN_EMAIL,
            'email_from_name'     => get_bloginfo('name'),
            'files_per_page'      => 20,
        ];

        if (!get_option('r35_hub_settings')) {
            update_option('r35_hub_settings', $defaults);
        }
    }
}
