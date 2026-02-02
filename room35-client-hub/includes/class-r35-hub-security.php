<?php
/**
 * Security utilities
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_Security {

    /**
     * Verify nonce
     *
     * @param string $action Nonce action
     * @param string $nonce_field Nonce field name
     * @return bool
     */
    public static function verify_nonce($action, $nonce_field = 'nonce') {
        $nonce = isset($_REQUEST[$nonce_field]) ? sanitize_text_field($_REQUEST[$nonce_field]) : '';
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Create nonce
     *
     * @param string $action Nonce action
     * @return string
     */
    public static function create_nonce($action) {
        return wp_create_nonce($action);
    }

    /**
     * Check if user can access a file
     *
     * @param int $user_id User ID
     * @param int $file_id File ID
     * @return bool
     */
    public static function can_user_access_file($user_id, $file_id) {
        global $wpdb;

        // Admins can access all files
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        $table = $wpdb->prefix . 'r35_hub_files';
        $file = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d AND status = 'active'",
            $file_id
        ));

        if (!$file) {
            return false;
        }

        // Global files are accessible to all logged-in users
        if ($file->is_global) {
            return true;
        }

        // Check if file is assigned to this user
        if ($file->assigned_to == $user_id) {
            return true;
        }

        // Check if this user uploaded the file (client uploads)
        if ($file->uploaded_by == $user_id && $file->upload_source === 'client') {
            return true;
        }

        return false;
    }

    /**
     * Check if current user can manage files (admin)
     *
     * @return bool
     */
    public static function can_manage_files() {
        return current_user_can('manage_options');
    }

    /**
     * Sanitize filename
     *
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    public static function sanitize_filename($filename) {
        // Remove path info
        $filename = basename($filename);

        // Use WordPress sanitization
        $filename = sanitize_file_name($filename);

        // Additional security - remove any remaining special chars
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        return $filename;
    }

    /**
     * Validate file extension
     *
     * @param string $filename Filename
     * @return bool
     */
    public static function validate_file_extension($filename) {
        $settings = get_option('r35_hub_settings', []);
        $allowed = isset($settings['allowed_extensions'])
            ? $settings['allowed_extensions']
            : ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowed);
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public static function get_client_ip() {
        $ip = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return sanitize_text_field($ip);
    }

    /**
     * Generate secure token for file downloads
     *
     * @param int $file_id File ID
     * @param int $user_id User ID
     * @return string
     */
    public static function generate_download_token($file_id, $user_id) {
        $data = $file_id . '|' . $user_id . '|' . time();
        return base64_encode($data) . '|' . wp_hash($data);
    }

    /**
     * Verify download token
     *
     * @param string $token Token to verify
     * @param int $file_id File ID
     * @param int $user_id User ID
     * @return bool
     */
    public static function verify_download_token($token, $file_id, $user_id) {
        $parts = explode('|', $token);
        if (count($parts) !== 2) {
            return false;
        }

        $data = base64_decode($parts[0]);
        $hash = $parts[1];

        if (wp_hash($data) !== $hash) {
            return false;
        }

        $data_parts = explode('|', $data);
        if (count($data_parts) !== 3) {
            return false;
        }

        // Token valid for 24 hours
        $token_time = intval($data_parts[2]);
        if (time() - $token_time > 86400) {
            return false;
        }

        return intval($data_parts[0]) === $file_id && intval($data_parts[1]) === $user_id;
    }
}
