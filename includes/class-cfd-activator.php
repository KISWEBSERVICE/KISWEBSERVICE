<?php
/**
 * Fired during plugin activation and deactivation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFD_Activator {

    /**
     * Activate the plugin.
     */
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for categories
        $categories_table = $wpdb->prefix . 'cfd_categories';
        $sql_categories = "CREATE TABLE IF NOT EXISTS $categories_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Table for files
        $files_table = $wpdb->prefix . 'cfd_files';
        $sql_files = "CREATE TABLE IF NOT EXISTS $files_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            filename varchar(255) NOT NULL,
            original_filename varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_type varchar(100) NOT NULL,
            file_size bigint(20) NOT NULL,
            category_id bigint(20),
            uploaded_by bigint(20) NOT NULL,
            is_universal tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY uploaded_by (uploaded_by)
        ) $charset_collate;";

        // Table for file assignments
        $assignments_table = $wpdb->prefix . 'cfd_file_assignments';
        $sql_assignments = "CREATE TABLE IF NOT EXISTS $assignments_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            file_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            assigned_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY file_id (file_id),
            KEY user_id (user_id),
            UNIQUE KEY file_user (file_id, user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_categories);
        dbDelta($sql_files);
        dbDelta($sql_assignments);

        // Create uploads directory
        $upload_dir = wp_upload_dir();
        $cfd_dir = $upload_dir['basedir'] . '/client-file-dashboard';
        if (!file_exists($cfd_dir)) {
            wp_mkdir_p($cfd_dir);
            // Add .htaccess for security
            $htaccess_content = "Options -Indexes\n<Files ~ \"\\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$\">\nDeny from all\n</Files>";
            file_put_contents($cfd_dir . '/.htaccess', $htaccess_content);
        }

        // Set default options
        add_option('cfd_version', CFD_VERSION);
    }

    /**
     * Deactivate the plugin.
     */
    public static function deactivate() {
        // Cleanup if needed
    }
}
