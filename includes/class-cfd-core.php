<?php
/**
 * The core plugin class.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFD_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once CFD_PLUGIN_DIR . 'includes/class-cfd-loader.php';
        require_once CFD_PLUGIN_DIR . 'includes/class-cfd-admin.php';
        require_once CFD_PLUGIN_DIR . 'includes/class-cfd-public.php';
        require_once CFD_PLUGIN_DIR . 'includes/class-cfd-database.php';

        $this->loader = new CFD_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new CFD_Admin();

        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // AJAX handlers
        $this->loader->add_action('wp_ajax_cfd_upload_file', $plugin_admin, 'ajax_upload_file');
        $this->loader->add_action('wp_ajax_cfd_delete_file', $plugin_admin, 'ajax_delete_file');
        $this->loader->add_action('wp_ajax_cfd_create_category', $plugin_admin, 'ajax_create_category');
        $this->loader->add_action('wp_ajax_cfd_delete_category', $plugin_admin, 'ajax_delete_category');
        $this->loader->add_action('wp_ajax_cfd_get_files', $plugin_admin, 'ajax_get_files');
        $this->loader->add_action('wp_ajax_cfd_assign_file', $plugin_admin, 'ajax_assign_file');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new CFD_Public();

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_shortcode('client_file_dashboard', $plugin_public, 'render_dashboard');

        // AJAX handlers for public
        $this->loader->add_action('wp_ajax_cfd_get_user_files', $plugin_public, 'ajax_get_user_files');
        $this->loader->add_action('wp_ajax_cfd_download_file', $plugin_public, 'ajax_download_file');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }
}
