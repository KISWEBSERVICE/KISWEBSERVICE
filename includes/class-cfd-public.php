<?php
/**
 * The public-facing functionality of the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFD_Public {

    private $db;

    public function __construct() {
        $this->db = new CFD_Database();
    }

    /**
     * Enqueue public styles
     */
    public function enqueue_styles() {
        if (is_page() && has_shortcode(get_post()->post_content, 'client_file_dashboard')) {
            wp_enqueue_style('cfd-public', CFD_PLUGIN_URL . 'assets/css/public.css', array(), CFD_VERSION);
        }
    }

    /**
     * Enqueue public scripts
     */
    public function enqueue_scripts() {
        if (is_page() && has_shortcode(get_post()->post_content, 'client_file_dashboard')) {
            wp_enqueue_script('cfd-public', CFD_PLUGIN_URL . 'assets/js/public.js', array('jquery'), CFD_VERSION, true);
            wp_localize_script('cfd-public', 'cfdPublic', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cfd_public_nonce'),
                'strings' => array(
                    'noFiles' => __('No files available at the moment.', 'client-file-dashboard'),
                    'downloadError' => __('Error downloading file.', 'client-file-dashboard'),
                )
            ));
        }
    }

    /**
     * Render dashboard shortcode
     */
    public function render_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<div class="cfd-dashboard-wrapper"><p class="cfd-login-notice">' .
                   __('Please log in to view your files.', 'client-file-dashboard') .
                   '</p></div>';
        }

        $user_id = get_current_user_id();
        $categories = $this->db->get_categories();
        $files = $this->db->get_user_files($user_id);

        ob_start();
        include CFD_PLUGIN_DIR . 'public/views/dashboard.php';
        return ob_get_clean();
    }

    /**
     * AJAX: Get user files
     */
    public function ajax_get_user_files() {
        check_ajax_referer('cfd_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $user_id = get_current_user_id();
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;

        $files = $this->db->get_user_files($user_id, $category_id);

        wp_send_json_success(array('files' => $files));
    }

    /**
     * AJAX: Download file
     */
    public function ajax_download_file() {
        check_ajax_referer('cfd_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : 0;
        $user_id = get_current_user_id();

        if (!$file_id) {
            wp_send_json_error(array('message' => 'Invalid file ID'));
        }

        $file = $this->db->get_file($file_id);

        if (!$file) {
            wp_send_json_error(array('message' => 'File not found'));
        }

        // Check if user has access to this file
        $user_files = $this->db->get_user_files($user_id);
        $has_access = false;

        foreach ($user_files as $user_file) {
            if ($user_file->id == $file_id) {
                $has_access = true;
                break;
            }
        }

        if (!$has_access) {
            wp_send_json_error(array('message' => 'You do not have access to this file'));
        }

        // Get file path
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/' . $file->file_path;

        if (!file_exists($file_path)) {
            wp_send_json_error(array('message' => 'File not found on server'));
        }

        // Return download URL with security token
        $token = wp_create_nonce('cfd_download_' . $file_id);
        $download_url = add_query_arg(array(
            'cfd_download' => $file_id,
            'token' => $token
        ), home_url());

        wp_send_json_success(array('download_url' => $download_url));
    }
}

// Handle file downloads
add_action('init', 'cfd_handle_file_download');
function cfd_handle_file_download() {
    if (!isset($_GET['cfd_download']) || !isset($_GET['token'])) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_die('Unauthorized');
    }

    $file_id = intval($_GET['cfd_download']);
    $token = sanitize_text_field($_GET['token']);

    // Verify token
    if (!wp_verify_nonce($token, 'cfd_download_' . $file_id)) {
        wp_die('Invalid security token');
    }

    $db = new CFD_Database();
    $file = $db->get_file($file_id);

    if (!$file) {
        wp_die('File not found');
    }

    // Check if user has access
    $user_id = get_current_user_id();
    $user_files = $db->get_user_files($user_id);
    $has_access = false;

    foreach ($user_files as $user_file) {
        if ($user_file->id == $file_id) {
            $has_access = true;
            break;
        }
    }

    if (!$has_access) {
        wp_die('You do not have access to this file');
    }

    // Get file path
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/' . $file->file_path;

    if (!file_exists($file_path)) {
        wp_die('File not found on server');
    }

    // Set headers and output file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file->original_filename . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    readfile($file_path);
    exit;
}
