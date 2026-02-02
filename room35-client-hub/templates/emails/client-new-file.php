<?php
/**
 * Email Template: Client New File Notification
 *
 * Available variables:
 * - $client_name
 * - $file_name
 * - $upload_date
 * - $dashboard_url
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
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .message {
            font-size: 15px;
            color: #555555;
            margin-bottom: 24px;
        }
        .file-box {
            background-color: #fafafa;
            border-left: 4px solid #000000;
            padding: 20px;
            margin: 24px 0;
        }
        .file-name {
            font-size: 16px;
            font-weight: 600;
            color: #000000;
            margin: 0 0 8px 0;
        }
        .file-date {
            font-size: 14px;
            color: #888888;
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
                <p class="greeting">Hello <?php echo esc_html($client_name); ?>,</p>

                <p class="message">A new file has been shared with you in your client portal.</p>

                <div class="file-box">
                    <p class="file-name"><?php echo esc_html($file_name); ?></p>
                    <p class="file-date">Uploaded on <?php echo esc_html($upload_date); ?></p>
                </div>

                <p class="message">You can view and download this file from your dashboard.</p>

                <a href="<?php echo esc_url($dashboard_url); ?>" class="btn">View My Files</a>
            </div>

            <div class="email-footer">
                <p class="footer-text">
                    This email was sent from <?php echo esc_html($site_name); ?>.<br>
                    If you have any questions, please contact us.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
