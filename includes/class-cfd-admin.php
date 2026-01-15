<?php
/**
 * The admin-specific functionality of the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFD_Admin {

    private $db;

    public function __construct() {
        $this->db = new CFD_Database();
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Client Files', 'client-file-dashboard'),
            __('Client Files', 'client-file-dashboard'),
            'manage_options',
            'client-file-dashboard',
            array($this, 'render_admin_page'),
            'dashicons-media-document',
            30
        );

        add_submenu_page(
            'client-file-dashboard',
            __('Manage Files', 'client-file-dashboard'),
            __('Manage Files', 'client-file-dashboard'),
            'manage_options',
            'client-file-dashboard',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'client-file-dashboard',
            __('Categories', 'client-file-dashboard'),
            __('Categories', 'client-file-dashboard'),
            'manage_options',
            'cfd-categories',
            array($this, 'render_categories_page')
        );
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_styles($hook) {
        if (strpos($hook, 'client-file-dashboard') === false && strpos($hook, 'cfd-categories') === false) {
            return;
        }
        wp_enqueue_style('cfd-admin', CFD_PLUGIN_URL . 'assets/css/admin.css', array(), CFD_VERSION);
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'client-file-dashboard') === false && strpos($hook, 'cfd-categories') === false) {
            return;
        }
        wp_enqueue_script('cfd-admin', CFD_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CFD_VERSION, true);
        wp_localize_script('cfd-admin', 'cfdAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cfd_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this file?', 'client-file-dashboard'),
                'confirmDeleteCategory' => __('Are you sure you want to delete this category?', 'client-file-dashboard'),
                'uploadSuccess' => __('File uploaded successfully!', 'client-file-dashboard'),
                'uploadError' => __('Error uploading file.', 'client-file-dashboard'),
            )
        ));
    }

    /**
     * Render main admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $categories = $this->db->get_categories();
        $files = $this->db->get_all_files();
        $users = get_users(array('fields' => array('ID', 'display_name', 'user_email')));

        include CFD_PLUGIN_DIR . 'admin/views/files-page.php';
    }

    /**
     * Render categories page
     */
    public function render_categories_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $categories = $this->db->get_categories();

        include CFD_PLUGIN_DIR . 'admin/views/categories-page.php';
    }

    /**
     * AJAX: Upload file
     */
    public function ajax_upload_file() {
        check_ajax_referer('cfd_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        if (!isset($_FILES['file'])) {
            wp_send_json_error(array('message' => 'No file uploaded'));
        }

        $file = $_FILES['file'];
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $is_universal = isset($_POST['is_universal']) && $_POST['is_universal'] === '1' ? 1 : 0;
        $assigned_users = isset($_POST['assigned_users']) ? json_decode(stripslashes($_POST['assigned_users']), true) : array();

        // Validate file type
        $allowed_types = array(
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );

        $file_type = wp_check_filetype($file['name']);
        $mime_type = $file['type'];

        if (!in_array($mime_type, $allowed_types)) {
            wp_send_json_error(array('message' => 'Invalid file type. Only documents and images are allowed.'));
        }

        // Upload file
        $upload_dir = wp_upload_dir();
        $cfd_dir = $upload_dir['basedir'] . '/client-file-dashboard';

        // Create unique filename
        $filename = time() . '_' . sanitize_file_name($file['name']);
        $file_path = $cfd_dir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Insert file record
            $file_id = $this->db->insert_file(array(
                'filename' => $filename,
                'original_filename' => $file['name'],
                'file_path' => 'client-file-dashboard/' . $filename,
                'file_type' => $file_type['ext'],
                'file_size' => $file['size'],
                'category_id' => $category_id,
                'uploaded_by' => get_current_user_id(),
                'is_universal' => $is_universal
            ));

            if ($file_id) {
                // Assign to users if not universal
                if (!$is_universal && !empty($assigned_users)) {
                    foreach ($assigned_users as $user_id) {
                        $this->db->assign_file_to_user($file_id, intval($user_id));
                    }
                }

                wp_send_json_success(array(
                    'message' => 'File uploaded successfully',
                    'file_id' => $file_id
                ));
            } else {
                unlink($file_path);
                wp_send_json_error(array('message' => 'Error saving file to database'));
            }
        } else {
            wp_send_json_error(array('message' => 'Error uploading file'));
        }
    }

    /**
     * AJAX: Delete file
     */
    public function ajax_delete_file() {
        check_ajax_referer('cfd_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;

        if (!$file_id) {
            wp_send_json_error(array('message' => 'Invalid file ID'));
        }

        // Get file info
        $file = $this->db->get_file($file_id);

        if ($file) {
            // Delete physical file
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'] . '/' . $file->file_path;
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Delete from database
            if ($this->db->delete_file($file_id)) {
                wp_send_json_success(array('message' => 'File deleted successfully'));
            } else {
                wp_send_json_error(array('message' => 'Error deleting file from database'));
            }
        } else {
            wp_send_json_error(array('message' => 'File not found'));
        }
    }

    /**
     * AJAX: Create category
     */
    public function ajax_create_category() {
        check_ajax_referer('cfd_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';

        if (empty($name)) {
            wp_send_json_error(array('message' => 'Category name is required'));
        }

        if ($this->db->create_category($name, $description)) {
            wp_send_json_success(array('message' => 'Category created successfully'));
        } else {
            wp_send_json_error(array('message' => 'Error creating category'));
        }
    }

    /**
     * AJAX: Delete category
     */
    public function ajax_delete_category() {
        check_ajax_referer('cfd_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

        if (!$category_id) {
            wp_send_json_error(array('message' => 'Invalid category ID'));
        }

        if ($this->db->delete_category($category_id)) {
            wp_send_json_success(array('message' => 'Category deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Error deleting category'));
        }
    }

    /**
     * AJAX: Get files
     */
    public function ajax_get_files() {
        check_ajax_referer('cfd_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $files = $this->db->get_all_files();
        wp_send_json_success(array('files' => $files));
    }

    /**
     * AJAX: Assign file to users
     */
    public function ajax_assign_file() {
        check_ajax_referer('cfd_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;
        $user_ids = isset($_POST['user_ids']) ? json_decode(stripslashes($_POST['user_ids']), true) : array();

        if (!$file_id) {
            wp_send_json_error(array('message' => 'Invalid file ID'));
        }

        // Remove all existing assignments
        global $wpdb;
        $assignments_table = $wpdb->prefix . 'cfd_file_assignments';
        $wpdb->delete($assignments_table, array('file_id' => $file_id), array('%d'));

        // Add new assignments
        foreach ($user_ids as $user_id) {
            $this->db->assign_file_to_user($file_id, intval($user_id));
        }

        wp_send_json_success(array('message' => 'File assignments updated'));
    }
}
