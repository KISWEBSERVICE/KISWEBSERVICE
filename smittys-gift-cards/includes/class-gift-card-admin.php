<?php
/**
 * Gift Card Admin
 * Handles admin dashboard and management features
 */

if (!defined('ABSPATH')) {
    exit;
}

class Smittys_Gift_Card_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_smittys_toggle_redemption', array($this, 'ajax_toggle_redemption'));
        add_action('wp_ajax_smittys_resend_gift_card', array($this, 'ajax_resend_gift_card'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Gift Cards', 'smittys-gift-cards'),
            __('Gift Cards', 'smittys-gift-cards'),
            'manage_woocommerce',
            'smittys-gift-cards',
            array($this, 'render_admin_page'),
            'dashicons-tickets-alt',
            56
        );

        add_submenu_page(
            'smittys-gift-cards',
            __('All Gift Cards', 'smittys-gift-cards'),
            __('All Gift Cards', 'smittys-gift-cards'),
            'manage_woocommerce',
            'smittys-gift-cards',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'smittys-gift-cards',
            __('Settings', 'smittys-gift-cards'),
            __('Settings', 'smittys-gift-cards'),
            'manage_options',
            'smittys-gift-cards-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'smittys-gift-cards') === false) {
            return;
        }

        wp_enqueue_style(
            'smittys-gift-cards-admin',
            SMITTYS_GC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SMITTYS_GC_VERSION
        );

        wp_enqueue_script(
            'smittys-gift-cards-admin',
            SMITTYS_GC_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SMITTYS_GC_VERSION,
            true
        );

        wp_localize_script('smittys-gift-cards-admin', 'smittysGiftCards', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smittys_gift_cards_nonce'),
        ));
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        global $wpdb;

        // Get filter parameters
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

        // Build query
        $where = array('1=1');
        $params = array();

        if ($search) {
            $where[] = "(recipient_name LIKE %s OR recipient_email LIKE %s OR product_variation_name LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        if ($status_filter === 'redeemed') {
            $where[] = "is_redeemed = 1";
        } elseif ($status_filter === 'active') {
            $where[] = "is_redeemed = 0";
        }

        if ($date_from) {
            $where[] = "DATE(purchase_date) >= %s";
            $params[] = $date_from;
        }

        if ($date_to) {
            $where[] = "DATE(purchase_date) <= %s";
            $params[] = $date_to;
        }

        $where_sql = implode(' AND ', $where);

        if (!empty($params)) {
            $where_sql = $wpdb->prepare($where_sql, $params);
        }

        // Get gift cards
        $gift_cards = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}smittys_gift_cards
            WHERE {$where_sql}
            ORDER BY purchase_date DESC
        ");

        // Get statistics
        $total_cards = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}smittys_gift_cards");
        $redeemed_cards = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}smittys_gift_cards WHERE is_redeemed = 1");
        $active_cards = $total_cards - $redeemed_cards;

        include SMITTYS_GC_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Handle form submission
        if (isset($_POST['smittys_gc_save_settings']) && check_admin_referer('smittys_gc_settings')) {
            update_option('smittys_gc_from_name', sanitize_text_field($_POST['from_name']));
            update_option('smittys_gc_from_email', sanitize_email($_POST['from_email']));
            update_option('smittys_gc_email_subject', sanitize_text_field($_POST['email_subject']));

            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'smittys-gift-cards') . '</p></div>';
        }

        $from_name = get_option('smittys_gc_from_name', get_bloginfo('name'));
        $from_email = get_option('smittys_gc_from_email', get_option('admin_email'));
        $email_subject = get_option('smittys_gc_email_subject', 'Your Gift Card from Smitty\'s E-Bikes');

        ?>
        <div class="wrap">
            <h1><?php _e('Gift Card Settings', 'smittys-gift-cards'); ?></h1>

            <form method="post" action="">
                <?php wp_nonce_field('smittys_gc_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="from_name"><?php _e('Email From Name', 'smittys-gift-cards'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="from_name" name="from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="from_email"><?php _e('Email From Address', 'smittys-gift-cards'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="from_email" name="from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="email_subject"><?php _e('Email Subject', 'smittys-gift-cards'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="email_subject" name="email_subject" value="<?php echo esc_attr($email_subject); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="smittys_gc_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'smittys-gift-cards'); ?>" />
                </p>
            </form>

            <hr>

            <h2><?php _e('Logo Setup', 'smittys-gift-cards'); ?></h2>
            <p><?php _e('To use your custom logo on gift cards, upload a PNG file named "smittys-logo.png" to:', 'smittys-gift-cards'); ?></p>
            <code><?php echo esc_html(SMITTYS_GC_PLUGIN_DIR . 'assets/images/'); ?></code>
            <p><?php _e('Recommended dimensions: 400px width minimum, transparent background.', 'smittys-gift-cards'); ?></p>

            <?php if (file_exists(SMITTYS_GC_PLUGIN_DIR . 'assets/images/smittys-logo.png')): ?>
                <p style="color: green;">✓ <?php _e('Logo file found!', 'smittys-gift-cards'); ?></p>
                <img src="<?php echo esc_url(SMITTYS_GC_PLUGIN_URL . 'assets/images/smittys-logo.png'); ?>" style="max-width: 300px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;" />
            <?php else: ?>
                <p style="color: orange;">⚠ <?php _e('No logo file found. Using text-based fallback.', 'smittys-gift-cards'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * AJAX: Toggle redemption status
     */
    public function ajax_toggle_redemption() {
        check_ajax_referer('smittys_gift_cards_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permission denied');
        }

        global $wpdb;

        $gift_card_id = intval($_POST['gift_card_id']);
        $is_redeemed = intval($_POST['is_redeemed']);

        $data = array('is_redeemed' => $is_redeemed);

        if ($is_redeemed) {
            $data['redeemed_date'] = current_time('mysql');
            $data['redeemed_by'] = wp_get_current_user()->display_name;
        } else {
            $data['redeemed_date'] = null;
            $data['redeemed_by'] = null;
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'smittys_gift_cards',
            $data,
            array('id' => $gift_card_id),
            array('%d', '%s', '%s'),
            array('%d')
        );

        if ($updated !== false) {
            wp_send_json_success(array(
                'message' => $is_redeemed ? __('Gift card marked as redeemed', 'smittys-gift-cards') : __('Gift card marked as active', 'smittys-gift-cards')
            ));
        } else {
            wp_send_json_error(__('Failed to update gift card', 'smittys-gift-cards'));
        }
    }

    /**
     * AJAX: Resend gift card email
     */
    public function ajax_resend_gift_card() {
        check_ajax_referer('smittys_gift_cards_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permission denied');
        }

        global $wpdb;

        $gift_card_id = intval($_POST['gift_card_id']);

        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}smittys_gift_cards WHERE id = %d",
            $gift_card_id
        ));

        if (!$gift_card) {
            wp_send_json_error(__('Gift card not found', 'smittys-gift-cards'));
        }

        $sent = do_action('smittys_send_gift_card_email', $gift_card_id, $gift_card->order_id);

        if ($sent) {
            wp_send_json_success(array('message' => __('Gift card email resent successfully', 'smittys-gift-cards')));
        } else {
            wp_send_json_error(__('Failed to send email', 'smittys-gift-cards'));
        }
    }
}
