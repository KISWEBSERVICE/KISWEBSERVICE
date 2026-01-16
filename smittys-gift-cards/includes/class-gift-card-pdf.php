<?php
/**
 * Gift Card PDF Generator
 * Generates PDF version of gift cards
 */

if (!defined('ABSPATH')) {
    exit;
}

class Smittys_Gift_Card_PDF {

    /**
     * Generate PDF from gift card data
     */
    public function generate_pdf($data) {
        // Check if Dompdf is available
        if (file_exists(SMITTYS_GC_PLUGIN_DIR . 'vendor/autoload.php')) {
            return $this->generate_with_dompdf($data);
        }

        // Fallback: Generate with TCPDF if available
        if (class_exists('TCPDF')) {
            return $this->generate_with_tcpdf($data);
        }

        // If no PDF library available, return false
        // Email will still be sent with HTML version
        return false;
    }

    /**
     * Generate PDF using Dompdf
     */
    private function generate_with_dompdf($data) {
        require_once SMITTYS_GC_PLUGIN_DIR . 'vendor/autoload.php';

        $dompdf = new \Dompdf\Dompdf();
        $html = $this->get_pdf_html($data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('Letter', 'landscape');
        $dompdf->render();

        // Save to temp file
        $upload_dir = wp_upload_dir();
        $temp_file = $upload_dir['basedir'] . '/gift-card-' . $data['gift_card_id'] . '-' . time() . '.pdf';

        file_put_contents($temp_file, $dompdf->output());

        return $temp_file;
    }

    /**
     * Generate PDF using TCPDF
     */
    private function generate_with_tcpdf($data) {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Smitty\'s E-Bikes');
        $pdf->SetTitle('Gift Card');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(20, 20, 20);

        // Add a page
        $pdf->AddPage();

        // Get HTML content
        $html = $this->get_pdf_html($data);

        // Print text using writeHTMLCell()
        $pdf->writeHTML($html, true, false, true, false, '');

        // Save to temp file
        $upload_dir = wp_upload_dir();
        $temp_file = $upload_dir['basedir'] . '/gift-card-' . $data['gift_card_id'] . '-' . time() . '.pdf';

        $pdf->Output($temp_file, 'F');

        return $temp_file;
    }

    /**
     * Get HTML for PDF
     */
    private function get_pdf_html($data) {
        $logo_path = SMITTYS_GC_PLUGIN_DIR . 'assets/images/smittys-logo.png';
        $logo_exists = file_exists($logo_path);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                }
                .gift-card {
                    width: 90%;
                    margin: 10px auto;
                    border: 5px solid #E31E24;
                    padding: 25px;
                    text-align: center;
                    background: #ffffff;
                }
                .logo {
                    margin-bottom: 15px;
                }
                .logo img {
                    max-width: 250px;
                    height: auto;
                }
                .logo-text {
                    font-size: 48px;
                    font-weight: bold;
                    color: #E31E24;
                    margin-bottom: 10px;
                }
                .logo-swoosh {
                    width: 200px;
                    height: 8px;
                    background: linear-gradient(to right, #E31E24, #F47920);
                    margin: 0 auto 15px;
                }
                h1 {
                    color: #1e3a5f;
                    font-size: 36px;
                    margin: 15px 0;
                }
                .gift-details {
                    background: #f8f8f8;
                    border: 3px dashed #E31E24;
                    padding: 20px;
                    margin: 20px 0;
                }
                .recipient {
                    font-size: 20px;
                    color: #333;
                    margin: 10px 0;
                }
                .product-name {
                    font-size: 22px;
                    color: #1e3a5f;
                    font-weight: bold;
                    margin: 15px 0;
                    line-height: 1.3;
                }
                .custom-message {
                    margin: 15px 0;
                    padding: 15px;
                    background: white;
                    font-size: 14px;
                    color: #666;
                    font-style: italic;
                }
                .sender {
                    color: #999;
                    margin-top: 8px;
                    font-size: 12px;
                }
                .footer {
                    margin-top: 20px;
                    padding-top: 15px;
                    border-top: 3px solid #E31E24;
                }
                .date-info {
                    color: #666;
                    font-size: 13px;
                    margin: 5px 0;
                }
                .expiration {
                    color: #E31E24;
                    font-size: 14px;
                    font-weight: bold;
                    margin: 8px 0;
                }
                .redemption {
                    color: #1e3a5f;
                    font-size: 16px;
                    font-weight: bold;
                    margin-top: 15px;
                }
                .contact {
                    color: #666;
                    font-size: 13px;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class="gift-card">
                <div class="logo">
                    <?php if ($logo_exists): ?>
                        <img src="<?php echo esc_attr($logo_path); ?>" alt="Smitty's E-Bikes" />
                    <?php else: ?>
                        <div class="logo-text">Smitty's</div>
                        <div class="logo-swoosh"></div>
                    <?php endif; ?>
                </div>

                <h1>GIFT CARD</h1>

                <div class="gift-details">
                    <div class="recipient">
                        <strong>To:</strong> <?php echo esc_html($data['recipient_name']); ?>
                    </div>

                    <div class="product-name">
                        <?php echo esc_html($data['product_variation_name']); ?>
                    </div>

                    <?php if (!empty($data['custom_message'])): ?>
                        <div class="custom-message">
                            "<?php echo nl2br(esc_html($data['custom_message'])); ?>"
                            <div class="sender">
                                - From <?php echo esc_html($data['sender_name']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="footer">
                    <div class="date-info">
                        <strong>Purchase Date:</strong> <?php echo esc_html($data['purchase_date']); ?>
                    </div>

                    <?php if ($data['expiration_date']): ?>
                        <div class="expiration">
                            <strong>Valid Until:</strong> <?php echo esc_html($data['expiration_date']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="redemption">
                        To redeem, please call us!
                    </div>

                    <div class="contact">
                        Smitty's E-Bikes<br>
                        Phone: (602) 320-6094<br>
                        Phone redemption only
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
