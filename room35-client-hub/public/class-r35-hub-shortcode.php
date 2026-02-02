<?php
/**
 * Shortcode Handler
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_Shortcode {

    /**
     * Initialize shortcode
     */
    public function init() {
        add_shortcode('room35_client_hub', [$this, 'render']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        // Only load on pages with our shortcode
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'room35_client_hub')) {
            return;
        }

        // Google Fonts - Poppins
        wp_enqueue_style(
            'r35-hub-poppins',
            'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'r35-hub-public',
            R35_HUB_PLUGIN_URL . 'public/css/r35-hub-public.css',
            ['r35-hub-poppins'],
            R35_HUB_VERSION
        );

        wp_enqueue_script(
            'r35-hub-public',
            R35_HUB_PLUGIN_URL . 'public/js/r35-hub-public.js',
            ['jquery'],
            R35_HUB_VERSION,
            true
        );

        wp_localize_script('r35-hub-public', 'r35HubClient', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('r35_hub_client_nonce'),
            'strings' => [
                'uploading' => __('Uploading...', 'room35-client-hub'),
                'uploadSuccess' => __('File uploaded successfully!', 'room35-client-hub'),
                'uploadError' => __('Upload failed. Please try again.', 'room35-client-hub'),
                'dropHere' => __('Drop file here', 'room35-client-hub'),
            ]
        ]);
    }

    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render($atts) {
        $atts = shortcode_atts([
            'show_upload' => 'true',
            'show_global' => 'true',
        ], $atts);

        // Check if user is logged in
        if (!is_user_logged_in()) {
            return $this->render_login_form();
        }

        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        // Get files
        $file_handler = new R35_Hub_File_Handler();
        $my_files = $file_handler->get_files_for_user($user_id);
        $my_uploads = $file_handler->get_client_uploads($user_id);
        $global_files = ($atts['show_global'] === 'true') ? $file_handler->get_global_files() : [];

        $show_upload = ($atts['show_upload'] === 'true');

        // Render dashboard
        ob_start();
        include R35_HUB_PLUGIN_DIR . 'public/partials/client-dashboard.php';
        return ob_get_clean();
    }

    /**
     * Render login form for non-logged-in users
     *
     * @return string HTML output
     */
    private function render_login_form() {
        ob_start();
        include R35_HUB_PLUGIN_DIR . 'public/partials/login-required.php';
        return ob_get_clean();
    }
}
