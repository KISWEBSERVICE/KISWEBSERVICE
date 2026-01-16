<?php
/**
 * Gift Card Email Template
 * Variables available: $data array with gift card information
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Gift Card from Smitty's E-Bikes</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #E31E24 0%, #1e3a5f 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">You've Received a Gift Card!</h1>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333; font-size: 18px; margin: 0 0 20px 0;">
                                Hi <?php echo esc_html($data['recipient_name']); ?>,
                            </p>

                            <p style="color: #666; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">
                                Great news! <?php echo esc_html($data['sender_name']); ?> has sent you a gift card for Smitty's E-Bikes.
                                Your gift card is attached as a PDF and also displayed below for easy printing.
                            </p>

                            <!-- Gift Card Display -->
                            <?php echo Smittys_Gift_Card_Email::get_inline_gift_card_html($data); ?>

                            <!-- Instructions -->
                            <div style="background-color: #f8f8f8; border-left: 4px solid #E31E24; padding: 20px; margin-top: 30px;">
                                <h3 style="color: #1e3a5f; margin: 0 0 10px 0; font-size: 18px;">How to Redeem</h3>
                                <p style="color: #666; margin: 0; font-size: 14px; line-height: 1.6;">
                                    This gift card can only be redeemed by phone. Please call us to book your reservation and
                                    mention that you have a gift card. Have this email or the attached PDF ready when you call.
                                </p>
                            </div>

                            <div style="margin-top: 30px; text-align: center;">
                                <p style="color: #999; font-size: 12px; margin: 10px 0;">
                                    Questions? Contact Smitty's E-Bikes
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #1e3a5f; padding: 20px; text-align: center;">
                            <p style="color: #ffffff; margin: 0; font-size: 14px;">
                                &copy; <?php echo date('Y'); ?> Smitty's E-Bikes. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
