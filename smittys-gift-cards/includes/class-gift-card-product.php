<?php
/**
 * Gift Card Product Meta
 * Handles product-level gift card settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class Smittys_Gift_Card_Product {

    public function __construct() {
        // Add gift card settings to product
        add_action('woocommerce_product_options_general_product_data', array($this, 'add_gift_card_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_gift_card_fields'));

        // Add gift card settings to variations
        add_action('woocommerce_variation_options_pricing', array($this, 'add_variation_gift_card_fields'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_variation_gift_card_fields'), 10, 2);
    }

    /**
     * Add gift card fields to product general tab
     */
    public function add_gift_card_fields() {
        global $post;

        echo '<div class="options_group">';

        woocommerce_wp_checkbox(array(
            'id' => '_is_gift_card',
            'label' => __('Gift Card Product', 'smittys-gift-cards'),
            'description' => __('Enable this to mark this product as a gift card', 'smittys-gift-cards'),
            'desc_tip' => true,
        ));

        woocommerce_wp_text_input(array(
            'id' => '_gift_card_expiration_months',
            'label' => __('Expiration (Months)', 'smittys-gift-cards'),
            'description' => __('Number of months until gift card expires. Leave blank for no expiration.', 'smittys-gift-cards'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '1',
                'min' => '0',
            ),
        ));

        echo '</div>';
    }

    /**
     * Save gift card fields
     */
    public function save_gift_card_fields($post_id) {
        $is_gift_card = isset($_POST['_is_gift_card']) ? 'yes' : 'no';
        update_post_meta($post_id, '_is_gift_card', $is_gift_card);

        if (isset($_POST['_gift_card_expiration_months'])) {
            update_post_meta($post_id, '_gift_card_expiration_months', sanitize_text_field($_POST['_gift_card_expiration_months']));
        }
    }

    /**
     * Add gift card fields to product variations
     */
    public function add_variation_gift_card_fields($loop, $variation_data, $variation) {
        $is_gift_card = get_post_meta($variation->ID, '_is_gift_card', true);
        $expiration = get_post_meta($variation->ID, '_gift_card_expiration_months', true);

        ?>
        <div class="form-row form-row-full">
            <label>
                <input type="checkbox" name="_variation_is_gift_card[<?php echo $loop; ?>]" value="yes" <?php checked($is_gift_card, 'yes'); ?> />
                <?php _e('This variation is a gift card', 'smittys-gift-cards'); ?>
            </label>
        </div>
        <div class="form-row form-row-full">
            <label><?php _e('Expiration (Months):', 'smittys-gift-cards'); ?></label>
            <input type="number" name="_variation_gift_card_expiration_months[<?php echo $loop; ?>]" value="<?php echo esc_attr($expiration); ?>" step="1" min="0" placeholder="Leave blank for no expiration" style="width: 100%;" />
        </div>
        <?php
    }

    /**
     * Save variation gift card fields
     */
    public function save_variation_gift_card_fields($variation_id, $loop) {
        $is_gift_card = isset($_POST['_variation_is_gift_card'][$loop]) ? 'yes' : 'no';
        update_post_meta($variation_id, '_is_gift_card', $is_gift_card);

        if (isset($_POST['_variation_gift_card_expiration_months'][$loop])) {
            update_post_meta($variation_id, '_gift_card_expiration_months', sanitize_text_field($_POST['_variation_gift_card_expiration_months'][$loop]));
        }
    }

    /**
     * Check if product or variation is a gift card
     */
    public static function is_gift_card($product_id, $variation_id = 0) {
        if ($variation_id > 0) {
            $is_gift_card = get_post_meta($variation_id, '_is_gift_card', true);
            if ($is_gift_card === 'yes') {
                return true;
            }
        }

        $is_gift_card = get_post_meta($product_id, '_is_gift_card', true);
        return $is_gift_card === 'yes';
    }

    /**
     * Get expiration months for a product/variation
     */
    public static function get_expiration_months($product_id, $variation_id = 0) {
        if ($variation_id > 0) {
            $months = get_post_meta($variation_id, '_gift_card_expiration_months', true);
            if ($months !== '') {
                return intval($months);
            }
        }

        $months = get_post_meta($product_id, '_gift_card_expiration_months', true);
        return $months !== '' ? intval($months) : 0;
    }
}
