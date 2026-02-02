<?php
/**
 * File Handler - Core file operations
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_File_Handler {

    /**
     * Allowed MIME types
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /**
     * Upload a file
     *
     * @param array $file $_FILES array element
     * @param int $assigned_to User ID to assign to (0 for global)
     * @param bool $is_global Whether this is a global file
     * @param string $source 'admin' or 'client'
     * @return int|WP_Error File ID or error
     */
    public function upload_file($file, $assigned_to = 0, $is_global = false, $source = 'admin') {
        // Validate the file
        $validation = $this->validate_file($file);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Get upload path
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/' . R35_HUB_UPLOAD_DIR;

        // Create user folder
        if ($assigned_to > 0 && !$is_global) {
            $user = get_userdata($assigned_to);
            if ($user) {
                $folder_name = sanitize_file_name(strtolower($user->user_login));
            } else {
                $folder_name = 'user-' . $assigned_to;
            }
        } else {
            $folder_name = 'global';
        }

        $target_dir = $base_path . '/' . $folder_name;

        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
            // Add index.php for security
            file_put_contents($target_dir . '/index.php', '<?php // Silence is golden');
        }

        // Generate unique filename
        $original_name = sanitize_file_name($file['name']);
        $unique_name = $this->generate_unique_filename($original_name);
        $target_path = $target_dir . '/' . $unique_name;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            return new WP_Error('upload_failed', __('Failed to move uploaded file.', 'room35-client-hub'));
        }

        // Get file info
        $file_type = mime_content_type($target_path);
        $file_size = filesize($target_path);
        $file_hash = hash_file('sha256', $target_path);

        // Store relative path
        $relative_path = R35_HUB_UPLOAD_DIR . '/' . $folder_name . '/' . $unique_name;

        // Insert into database
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        $inserted = $wpdb->insert(
            $table,
            [
                'file_name' => $unique_name,
                'original_name' => $original_name,
                'file_path' => $relative_path,
                'file_type' => $file_type,
                'file_size' => $file_size,
                'file_hash' => $file_hash,
                'uploaded_by' => get_current_user_id(),
                'assigned_to' => $is_global ? null : ($assigned_to > 0 ? $assigned_to : null),
                'is_global' => $is_global ? 1 : 0,
                'upload_source' => $source,
                'status' => 'active',
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d', '%s', '%s']
        );

        if (!$inserted) {
            // Clean up the file if DB insert failed
            @unlink($target_path);
            return new WP_Error('db_error', __('Failed to save file information.', 'room35-client-hub'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Validate uploaded file
     *
     * @param array $file $_FILES array element
     * @return true|WP_Error
     */
    public function validate_file($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => __('File exceeds server upload limit.', 'room35-client-hub'),
                UPLOAD_ERR_FORM_SIZE => __('File exceeds form upload limit.', 'room35-client-hub'),
                UPLOAD_ERR_PARTIAL => __('File was only partially uploaded.', 'room35-client-hub'),
                UPLOAD_ERR_NO_FILE => __('No file was uploaded.', 'room35-client-hub'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing temporary folder.', 'room35-client-hub'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'room35-client-hub'),
            ];
            $message = isset($error_messages[$file['error']])
                ? $error_messages[$file['error']]
                : __('Unknown upload error.', 'room35-client-hub');
            return new WP_Error('upload_error', $message);
        }

        // Check file size (24MB max)
        $settings = get_option('r35_hub_settings', []);
        $max_size_mb = isset($settings['max_file_size']) ? $settings['max_file_size'] : 24;
        $max_size = $max_size_mb * 1024 * 1024;

        if ($file['size'] > $max_size) {
            return new WP_Error(
                'file_too_large',
                sprintf(__('File exceeds %dMB limit.', 'room35-client-hub'), $max_size_mb)
            );
        }

        // Check file size is not zero
        if ($file['size'] === 0) {
            return new WP_Error('empty_file', __('File is empty.', 'room35-client-hub'));
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mime_type, self::ALLOWED_MIME_TYPES)) {
            return new WP_Error(
                'invalid_type',
                __('File type not allowed. Allowed types: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG', 'room35-client-hub')
            );
        }

        // Validate extension matches MIME
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $expected_ext = self::ALLOWED_MIME_TYPES[$mime_type];

        // Handle jpg/jpeg variation
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        if ($expected_ext !== $ext) {
            return new WP_Error(
                'mime_mismatch',
                __('File extension does not match file content.', 'room35-client-hub')
            );
        }

        // Check for path traversal attempts
        if (preg_match('/\.\./', $file['name']) || preg_match('/[\/\\\\]/', $file['name'])) {
            return new WP_Error('invalid_filename', __('Invalid filename.', 'room35-client-hub'));
        }

        return true;
    }

    /**
     * Generate unique filename
     *
     * @param string $original_name Original filename
     * @return string Unique filename
     */
    private function generate_unique_filename($original_name) {
        $ext = pathinfo($original_name, PATHINFO_EXTENSION);
        $name = pathinfo($original_name, PATHINFO_FILENAME);
        $name = sanitize_file_name($name);

        return $name . '_' . time() . '_' . wp_generate_password(8, false) . '.' . $ext;
    }

    /**
     * Get a file by ID
     *
     * @param int $file_id File ID
     * @return object|null
     */
    public function get_file($file_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d AND status = 'active'",
            $file_id
        ));
    }

    /**
     * Get files for a specific user
     *
     * @param int $user_id User ID
     * @return array
     */
    public function get_files_for_user($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        // Get files assigned to this user (from admin)
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table}
            WHERE assigned_to = %d
            AND upload_source = 'admin'
            AND status = 'active'
            ORDER BY created_at DESC",
            $user_id
        ));
    }

    /**
     * Get files uploaded by a client
     *
     * @param int $user_id User ID
     * @return array
     */
    public function get_client_uploads($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table}
            WHERE uploaded_by = %d
            AND upload_source = 'client'
            AND status = 'active'
            ORDER BY created_at DESC",
            $user_id
        ));
    }

    /**
     * Get global files
     *
     * @return array
     */
    public function get_global_files() {
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        return $wpdb->get_results(
            "SELECT * FROM {$table}
            WHERE is_global = 1
            AND status = 'active'
            ORDER BY created_at DESC"
        );
    }

    /**
     * Get all files (for admin)
     *
     * @param array $args Filter arguments
     * @return array
     */
    public function get_all_files($args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        $where = ["status = 'active'"];
        $values = [];

        if (!empty($args['assigned_to'])) {
            $where[] = "assigned_to = %d";
            $values[] = $args['assigned_to'];
        }

        if (!empty($args['upload_source'])) {
            $where[] = "upload_source = %s";
            $values[] = $args['upload_source'];
        }

        if (isset($args['is_global'])) {
            $where[] = "is_global = %d";
            $values[] = $args['is_global'] ? 1 : 0;
        }

        $where_clause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY created_at DESC";

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Delete a file
     *
     * @param int $file_id File ID
     * @return bool
     */
    public function delete_file($file_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_files';

        // Get file info first
        $file = $this->get_file($file_id);
        if (!$file) {
            return false;
        }

        // Delete physical file
        $upload_dir = wp_upload_dir();
        $full_path = $upload_dir['basedir'] . '/' . $file->file_path;

        if (file_exists($full_path)) {
            @unlink($full_path);
        }

        // Soft delete in database
        $result = $wpdb->update(
            $table,
            ['status' => 'deleted'],
            ['id' => $file_id],
            ['%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Process download request
     *
     * @param string $param Download parameter (file_id or token)
     */
    public function process_download_request($param) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_die(__('You must be logged in to download files.', 'room35-client-hub'), 403);
        }

        $parts = explode('-', $param);
        $file_id = absint($parts[0]);
        $action = isset($parts[1]) ? $parts[1] : 'download';

        if (!$file_id) {
            wp_die(__('Invalid file request.', 'room35-client-hub'), 400);
        }

        $user_id = get_current_user_id();

        // Check permission
        if (!R35_Hub_Security::can_user_access_file($user_id, $file_id)) {
            wp_die(__('You do not have permission to access this file.', 'room35-client-hub'), 403);
        }

        // Get file
        $file = $this->get_file($file_id);
        if (!$file) {
            wp_die(__('File not found.', 'room35-client-hub'), 404);
        }

        // Get full path
        $upload_dir = wp_upload_dir();
        $full_path = $upload_dir['basedir'] . '/' . $file->file_path;

        if (!file_exists($full_path)) {
            wp_die(__('File not found on server.', 'room35-client-hub'), 404);
        }

        // Log download
        $this->log_download($file_id, $user_id);

        // Update download count
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}r35_hub_files SET download_count = download_count + 1 WHERE id = %d",
            $file_id
        ));

        // Serve the file
        $this->serve_file($full_path, $file->original_name, $file->file_type, $action);
    }

    /**
     * Serve a file for download or view
     *
     * @param string $path Full file path
     * @param string $filename Original filename
     * @param string $mime_type MIME type
     * @param string $action 'download' or 'view'
     */
    private function serve_file($path, $filename, $mime_type, $action = 'download') {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        if ($action === 'view' && $this->is_viewable_type($mime_type)) {
            // Inline display for PDFs and images
            header('Content-Disposition: inline; filename="' . $filename . '"');
        } else {
            // Force download
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        // Output file
        readfile($path);
        exit;
    }

    /**
     * Check if file type is viewable in browser
     *
     * @param string $mime_type MIME type
     * @return bool
     */
    private function is_viewable_type($mime_type) {
        $viewable = [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];
        return in_array($mime_type, $viewable);
    }

    /**
     * Log file download
     *
     * @param int $file_id File ID
     * @param int $user_id User ID
     */
    private function log_download($file_id, $user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'r35_hub_download_log';

        $wpdb->insert(
            $table,
            [
                'file_id' => $file_id,
                'user_id' => $user_id,
                'ip_address' => R35_Hub_Security::get_client_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT'])
                    ? substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 500)
                    : '',
            ],
            ['%d', '%d', '%s', '%s']
        );
    }

    /**
     * Get secure download URL
     *
     * @param int $file_id File ID
     * @param string $action 'download' or 'view'
     * @return string
     */
    public function get_secure_download_url($file_id, $action = 'download') {
        return home_url('/r35-download/' . $file_id . '-' . $action . '/');
    }

    /**
     * Format file size for display
     *
     * @param int $bytes File size in bytes
     * @return string Formatted size
     */
    public static function format_file_size($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get file type icon class
     *
     * @param string $mime_type MIME type
     * @return string Icon class
     */
    public static function get_file_icon($mime_type) {
        $icons = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'doc',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xls',
            'image/jpeg' => 'image',
            'image/png' => 'image',
        ];

        return isset($icons[$mime_type]) ? $icons[$mime_type] : 'file';
    }
}
