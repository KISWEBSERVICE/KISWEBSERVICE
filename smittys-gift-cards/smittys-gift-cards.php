<?php
/**
 * Plugin Name: Smitty's Gift Cards
 * Plugin URI: https://smittysebikes.com
 * Description: Digital gift card system for WooCommerce with email delivery and admin management
 * Version: 1.0.0
 * Author: Smitty's E-Bikes
 * Author URI: https://smittysebikes.com
 * Text Domain: smittys-gift-cards
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SMITTYS_GC_VERSION', '1.0.0');
define('SMITTYS_GC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMITTYS_GC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMITTYS_GC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Smittys_Gift_Cards {

    /**
     * Single instance of the class
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Load plugin files
        $this->load_dependencies();

        // Initialize components
        $this->init_hooks();

        // Create custom database table
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        require_once SMITTYS_GC_PLUGIN_DIR . 'includes/class-gift-card-product.php';
        require_once SMITTYS_GC_PLUGIN_DIR . 'includes/class-gift-card-checkout.php';
        require_once SMITTYS_GC_PLUGIN_DIR . 'includes/class-gift-card-email.php';
        require_once SMITTYS_GC_PLUGIN_DIR . 'includes/class-gift-card-pdf.php';
        require_once SMITTYS_GC_PLUGIN_DIR . 'includes/class-gift-card-admin.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        new Smittys_Gift_Card_Product();
        new Smittys_Gift_Card_Checkout();
        new Smittys_Gift_Card_Email();
        new Smittys_Gift_Card_Admin();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'smittys_gift_cards';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            variation_id bigint(20) DEFAULT 0,
            recipient_name varchar(255) NOT NULL,
            recipient_email varchar(255) NOT NULL,
            custom_message text,
            purchase_date datetime NOT NULL,
            expiration_date datetime DEFAULT NULL,
            product_variation_name text,
            is_redeemed tinyint(1) DEFAULT 0,
            redeemed_date datetime DEFAULT NULL,
            redeemed_by varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY recipient_email (recipient_email),
            KEY is_redeemed (is_redeemed)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Set default options
        add_option('smittys_gc_from_name', get_bloginfo('name'));
        add_option('smittys_gc_from_email', get_option('admin_email'));
        add_option('smittys_gc_email_subject', 'Your Gift Card from Smitty\'s E-Bikes');
    }

    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php _e('Smitty\'s Gift Cards requires WooCommerce to be installed and active.', 'smittys-gift-cards'); ?></p>
        </div>
        <?php
    }
}

// Initialize the plugin
Smittys_Gift_Cards::get_instance();
