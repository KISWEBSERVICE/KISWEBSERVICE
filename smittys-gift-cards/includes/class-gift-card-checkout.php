<?php
/**
 * Gift Card Checkout
 * Handles checkout fields and order processing for gift cards
 */

if (!defined('ABSPATH')) {
    exit;
}

class Smittys_Gift_Card_Checkout {

    public function __construct() {
        // Add custom fields to checkout
        add_action('woocommerce_after_order_notes', array($this, 'add_gift_card_fields'));

        // Validate fields
        add_action('woocommerce_checkout_process', array($this, 'validate_gift_card_fields'));

        // Save fields to order
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_gift_card_fields'));

        // Process gift card on order completion
        add_action('woocommerce_order_status_completed', array($this, 'process_gift_card_order'));
        add_action('woocommerce_order_status_processing', array($this, 'process_gift_card_order'));

        // Display gift card info in admin order page
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_gift_card_info_in_admin'));
    }

    /**
     * Check if cart contains gift card
     */
    private function cart_contains_gift_card() {
        if (!WC()->cart) {
            return false;
        }

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;

            if (Smittys_Gift_Card_Product::is_gift_card($product_id, $variation_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add gift card fields to checkout
     */
    public function add_gift_card_fields($checkout) {
        if (!$this->cart_contains_gift_card()) {
            return;
        }

        echo '<div id="gift_card_recipient_info"><h3>' . __('Gift Card Recipient Information', 'smittys-gift-cards') . '</h3>';

        woocommerce_form_field('gift_card_recipient_name', array(
            'type' => 'text',
            'class' => array('form-row-wide'),
            'label' => __('Recipient Name', 'smittys-gift-cards'),
            'placeholder' => __('Enter recipient\'s name', 'smittys-gift-cards'),
            'required' => true,
        ), $checkout->get_value('gift_card_recipient_name'));

        woocommerce_form_field('gift_card_recipient_email', array(
            'type' => 'email',
            'class' => array('form-row-wide'),
            'label' => __('Recipient Email', 'smittys-gift-cards'),
            'placeholder' => __('Enter recipient\'s email address', 'smittys-gift-cards'),
            'required' => true,
        ), $checkout->get_value('gift_card_recipient_email'));

        woocommerce_form_field('gift_card_custom_message', array(
            'type' => 'textarea',
            'class' => array('form-row-wide'),
            'label' => __('Personal Message (Optional)', 'smittys-gift-cards'),
            'placeholder' => __('Add a personal message for the recipient', 'smittys-gift-cards'),
            'required' => false,
        ), $checkout->get_value('gift_card_custom_message'));

        echo '</div>';
    }

    /**
     * Validate gift card fields
     */
    public function validate_gift_card_fields() {
        if (!$this->cart_contains_gift_card()) {
            return;
        }

        if (empty($_POST['gift_card_recipient_name'])) {
            wc_add_notice(__('Please enter the recipient\'s name for the gift card.', 'smittys-gift-cards'), 'error');
        }

        if (empty($_POST['gift_card_recipient_email'])) {
            wc_add_notice(__('Please enter the recipient\'s email address for the gift card.', 'smittys-gift-cards'), 'error');
        } elseif (!is_email($_POST['gift_card_recipient_email'])) {
            wc_add_notice(__('Please enter a valid email address for the gift card recipient.', 'smittys-gift-cards'), 'error');
        }
    }

    /**
     * Save gift card fields to order meta
     */
    public function save_gift_card_fields($order_id) {
        if (!empty($_POST['gift_card_recipient_name'])) {
            update_post_meta($order_id, '_gift_card_recipient_name', sanitize_text_field($_POST['gift_card_recipient_name']));
        }
        if (!empty($_POST['gift_card_recipient_email'])) {
            update_post_meta($order_id, '_gift_card_recipient_email', sanitize_email($_POST['gift_card_recipient_email']));
        }
        if (!empty($_POST['gift_card_custom_message'])) {
            update_post_meta($order_id, '_gift_card_custom_message', sanitize_textarea_field($_POST['gift_card_custom_message']));
        }
    }

    /**
     * Process gift card order
     */
    public function process_gift_card_order($order_id) {
        global $wpdb;

        $order = wc_get_order($order_id);
        $items = $order->get_items();

        $recipient_name = get_post_meta($order_id, '_gift_card_recipient_name', true);
        $recipient_email = get_post_meta($order_id, '_gift_card_recipient_email', true);
        $custom_message = get_post_meta($order_id, '_gift_card_custom_message', true);

        // Check if gift cards already created for this order
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}smittys_gift_cards WHERE order_id = %d",
            $order_id
        ));

        if ($existing > 0) {
            return; // Already processed
        }

        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id();

            if (!Smittys_Gift_Card_Product::is_gift_card($product_id, $variation_id)) {
                continue;
            }

            // Get expiration date
            $expiration_months = Smittys_Gift_Card_Product::get_expiration_months($product_id, $variation_id);
            $purchase_date = $order->get_date_created();
            $expiration_date = null;

            if ($expiration_months > 0) {
                $expiration_date = clone $purchase_date;
                $expiration_date->modify("+{$expiration_months} months");
            }

            // Get product variation name
            $product = $item->get_product();
            $product_variation_name = $product->get_name();

            // Create multiple gift card records based on quantity
            $quantity = $item->get_quantity();
            for ($i = 0; $i < $quantity; $i++) {
                $wpdb->insert(
                    $wpdb->prefix . 'smittys_gift_cards',
                    array(
                        'order_id' => $order_id,
                        'product_id' => $product_id,
                        'variation_id' => $variation_id,
                        'recipient_name' => $recipient_name,
                        'recipient_email' => $recipient_email,
                        'custom_message' => $custom_message,
                        'purchase_date' => $purchase_date->date('Y-m-d H:i:s'),
                        'expiration_date' => $expiration_date ? $expiration_date->date('Y-m-d H:i:s') : null,
                        'product_variation_name' => $product_variation_name,
                        'is_redeemed' => 0,
                    ),
                    array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
                );

                $gift_card_id = $wpdb->insert_id;

                // Send email for this gift card
                do_action('smittys_send_gift_card_email', $gift_card_id, $order_id);
            }
        }
    }

    /**
     * Display gift card info in admin order page
     */
    public function display_gift_card_info_in_admin($order) {
        $recipient_name = get_post_meta($order->get_id(), '_gift_card_recipient_name', true);
        $recipient_email = get_post_meta($order->get_id(), '_gift_card_recipient_email', true);
        $custom_message = get_post_meta($order->get_id(), '_gift_card_custom_message', true);

        if ($recipient_name || $recipient_email) {
            echo '<div class="order_data_column">';
            echo '<h3>' . __('Gift Card Information', 'smittys-gift-cards') . '</h3>';

            if ($recipient_name) {
                echo '<p><strong>' . __('Recipient Name:', 'smittys-gift-cards') . '</strong> ' . esc_html($recipient_name) . '</p>';
            }

            if ($recipient_email) {
                echo '<p><strong>' . __('Recipient Email:', 'smittys-gift-cards') . '</strong> ' . esc_html($recipient_email) . '</p>';
            }

            if ($custom_message) {
                echo '<p><strong>' . __('Custom Message:', 'smittys-gift-cards') . '</strong><br>' . nl2br(esc_html($custom_message)) . '</p>';
            }

            echo '</div>';
        }
    }
}
