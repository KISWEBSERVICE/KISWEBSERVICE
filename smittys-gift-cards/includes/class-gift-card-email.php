<?php
/**
 * Gift Card Email
 * Handles email generation and sending
 */

if (!defined('ABSPATH')) {
    exit;
}

class Smittys_Gift_Card_Email {

    public function __construct() {
        add_action('smittys_send_gift_card_email', array($this, 'send_gift_card_email'), 10, 2);
    }

    /**
     * Send gift card email
     */
    public function send_gift_card_email($gift_card_id, $order_id) {
        global $wpdb;

        // Get gift card data
        $gift_card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}smittys_gift_cards WHERE id = %d",
            $gift_card_id
        ));

        if (!$gift_card) {
            return false;
        }

        // Get order data
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        // Prepare email data
        $email_data = array(
            'recipient_name' => $gift_card->recipient_name,
            'recipient_email' => $gift_card->recipient_email,
            'custom_message' => $gift_card->custom_message,
            'product_variation_name' => $gift_card->product_variation_name,
            'purchase_date' => date('F j, Y', strtotime($gift_card->purchase_date)),
            'expiration_date' => $gift_card->expiration_date ? date('F j, Y', strtotime($gift_card->expiration_date)) : null,
            'gift_card_id' => $gift_card_id,
            'order_id' => $order_id,
            'sender_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        );

        // Generate HTML email body
        $html_body = $this->get_email_template($email_data);

        // Generate PDF
        require_once SMITTYS_GC_PLUGIN_DIR . 'includes/class-gift-card-pdf.php';
        $pdf_generator = new Smittys_Gift_Card_PDF();
        $pdf_path = $pdf_generator->generate_pdf($email_data);

        // Email settings
        $to = $gift_card->recipient_email;
        $subject = get_option('smittys_gc_email_subject', 'Your Gift Card from Smitty\'s E-Bikes');
        $from_name = get_option('smittys_gc_from_name', get_bloginfo('name'));
        $from_email = get_option('smittys_gc_from_email', get_option('admin_email'));

        // Headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        );

        // Attachments
        $attachments = array();
        if ($pdf_path && file_exists($pdf_path)) {
            $attachments[] = $pdf_path;
        }

        // Send email
        $sent = wp_mail($to, $subject, $html_body, $headers, $attachments);

        // Clean up PDF file
        if ($pdf_path && file_exists($pdf_path)) {
            @unlink($pdf_path);
        }

        return $sent;
    }

    /**
     * Get email template HTML
     */
    private function get_email_template($data) {
        ob_start();
        include SMITTYS_GC_PLUGIN_DIR . 'templates/email-template.php';
        return ob_get_clean();
    }

    /**
     * Get inline gift card HTML for email body
     */
    public static function get_inline_gift_card_html($data) {
        $logo_url = SMITTYS_GC_PLUGIN_URL . 'assets/images/smittys-logo.png';

        // Check if custom logo exists
        if (!file_exists(SMITTYS_GC_PLUGIN_DIR . 'assets/images/smittys-logo.png')) {
            // Use text-based logo as fallback
            $logo_html = '<div style="font-size: 48px; font-weight: bold; color: #E31E24; font-family: \'Brush Script MT\', cursive; margin-bottom: 10px;">Smitty\'s</div>';
            $logo_html .= '<div style="width: 200px; height: 10px; background: linear-gradient(to right, #E31E24, #F47920); margin: 0 auto 20px;"></div>';
        } else {
            $logo_html = '<img src="' . esc_url($logo_url) . '" alt="Smitty\'s E-Bikes" style="max-width: 300px; height: auto; margin-bottom: 20px;" />';
        }

        ob_start();
        ?>
        <div style="max-width: 800px; margin: 0 auto; background: #ffffff; border: 3px solid #E31E24; border-radius: 10px; padding: 40px; text-align: center; font-family: Arial, sans-serif;">
            <?php echo $logo_html; ?>

            <h1 style="color: #1e3a5f; font-size: 36px; margin: 20px 0;">GIFT CARD</h1>

            <div style="background: #f8f8f8; border: 2px dashed #E31E24; border-radius: 8px; padding: 30px; margin: 30px 0;">
                <p style="font-size: 20px; color: #333; margin: 10px 0;">
                    <strong>To:</strong> <?php echo esc_html($data['recipient_name']); ?>
                </p>

                <p style="font-size: 18px; color: #1e3a5f; margin: 20px 0; font-weight: bold;">
                    <?php echo esc_html($data['product_variation_name']); ?>
                </p>

                <?php if (!empty($data['custom_message'])): ?>
                    <div style="margin: 20px 0; padding: 20px; background: white; border-radius: 5px;">
                        <p style="color: #666; font-style: italic; margin: 0;">
                            "<?php echo nl2br(esc_html($data['custom_message'])); ?>"
                        </p>
                        <p style="color: #999; margin-top: 10px; font-size: 14px;">
                            - From <?php echo esc_html($data['sender_name']); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #E31E24;">
                <p style="color: #666; font-size: 14px; margin: 5px 0;">
                    <strong>Purchase Date:</strong> <?php echo esc_html($data['purchase_date']); ?>
                </p>

                <?php if ($data['expiration_date']): ?>
                    <p style="color: #E31E24; font-size: 14px; margin: 5px 0; font-weight: bold;">
                        <strong>Valid Until:</strong> <?php echo esc_html($data['expiration_date']); ?>
                    </p>
                <?php endif; ?>

                <p style="color: #1e3a5f; font-size: 16px; margin-top: 20px; font-weight: bold;">
                    To redeem, please call us!
                </p>

                <p style="color: #666; font-size: 14px; margin: 10px 0;">
                    Smitty's E-Bikes<br>
                    Phone redemption only
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
