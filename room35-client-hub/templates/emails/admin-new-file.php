<?php
/**
 * Email Template: Admin New File Notification
 *
 * Available variables:
 * - $client_name
 * - $client_email
 * - $file_name
 * - $file_size
 * - $upload_date
 * - $admin_url
 * - $site_name
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_name); ?></title>
    <style>
        /* Reset */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f5f5f5;
            padding: 40px 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
        }
        .email-header {
            background-color: #000000;
            color: #ffffff;
            padding: 30px 40px;
            text-align: left;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 500;
            letter-spacing: -0.02em;
        }
        .email-body {
            padding: 40px;
        }
        .notification-badge {
            display: inline-block;
            background-color: #16a34a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 4px 10px;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }
        .message {
            font-size: 15px;
            color: #555555;
            margin-bottom: 24px;
        }
        .details-box {
            background-color: #fafafa;
            border-left: 4px solid #16a34a;
            padding: 20px;
            margin: 24px 0;
        }
        .detail-row {
            margin-bottom: 12px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-size: 13px;
            font-weight: 600;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 15px;
            color: #000000;
            margin: 0;
        }
        .btn {
            display: inline-block;
            background-color: #000000;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            font-size: 14px;
            font-weight: 500;
            margin-top: 8px;
        }
        .btn:hover {
            background-color: #333333;
        }
        .email-footer {
            padding: 30px 40px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
        }
        .footer-text {
            font-size: 13px;
            color: #888888;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1><?php echo esc_html($site_name); ?></h1>
            </div>

            <div class="email-body">
                <span class="notification-badge">New Upload</span>

                <p class="message">A client has uploaded a new file to the portal.</p>

                <div class="details-box">
                    <div class="detail-row">
                        <p class="detail-label">Client</p>
                        <p class="detail-value"><?php echo esc_html($client_name); ?> (<?php echo esc_html($client_email); ?>)</p>
                    </div>
                    <div class="detail-row">
                        <p class="detail-label">File Name</p>
                        <p class="detail-value"><?php echo esc_html($file_name); ?></p>
                    </div>
                    <div class="detail-row">
                        <p class="detail-label">File Size</p>
                        <p class="detail-value"><?php echo esc_html($file_size); ?></p>
                    </div>
                    <div class="detail-row">
                        <p class="detail-label">Uploaded</p>
                        <p class="detail-value"><?php echo esc_html($upload_date); ?></p>
                    </div>
                </div>

                <a href="<?php echo esc_url($admin_url); ?>" class="btn">View in Dashboard</a>
            </div>

            <div class="email-footer">
                <p class="footer-text">
                    This is an automated notification from <?php echo esc_html($site_name); ?>.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
