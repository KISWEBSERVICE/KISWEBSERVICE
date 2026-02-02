<?php
/**
 * Email Notifications
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_Email_Notifications {

    /**
     * Notify client when admin uploads a file for them
     *
     * @param int $file_id File ID
     * @param int $client_id Client user ID
     * @return bool
     */
    public function notify_client_new_file($file_id, $client_id) {
        $settings = get_option('r35_hub_settings', []);

        // Check if notifications are enabled
        if (isset($settings['email_notifications']) && !$settings['email_notifications']) {
            return false;
        }

        $client = get_userdata($client_id);
        if (!$client) {
            return false;
        }

        $file_handler = new R35_Hub_File_Handler();
        $file = $file_handler->get_file($file_id);
        if (!$file) {
            return false;
        }

        $dashboard_page_id = get_option('r35_hub_dashboard_page_id');
        $dashboard_url = $dashboard_page_id ? get_permalink($dashboard_page_id) : home_url();

        $site_name = get_bloginfo('name');
        $client_name = R35_Hub_User_Role::get_user_display_name($client_id);

        // Build email
        $subject = sprintf(
            __('[%s] A new file has been shared with you', 'room35-client-hub'),
            $site_name
        );

        $template_data = [
            'client_name' => $client_name,
            'file_name' => $file->original_name,
            'upload_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($file->created_at)),
            'dashboard_url' => $dashboard_url,
            'site_name' => $site_name,
        ];

        $message = $this->get_email_template('client-new-file', $template_data);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->get_from_name() . ' <' . $this->get_from_email() . '>',
        ];

        return wp_mail($client->user_email, $subject, $message, $headers);
    }

    /**
     * Notify admin when client uploads a file
     *
     * @param int $file_id File ID
     * @param int $client_id Client user ID
     * @return bool
     */
    public function notify_admin_new_file($file_id, $client_id) {
        $settings = get_option('r35_hub_settings', []);

        // Check if notifications are enabled
        if (isset($settings['email_notifications']) && !$settings['email_notifications']) {
            return false;
        }

        $client = get_userdata($client_id);
        if (!$client) {
            return false;
        }

        $file_handler = new R35_Hub_File_Handler();
        $file = $file_handler->get_file($file_id);
        if (!$file) {
            return false;
        }

        $admin_url = admin_url('admin.php?page=r35-hub-files');
        $site_name = get_bloginfo('name');
        $client_name = R35_Hub_User_Role::get_user_display_name($client_id);

        // Build email
        $subject = sprintf(
            __('[%s] New file uploaded by %s', 'room35-client-hub'),
            $site_name,
            $client_name
        );

        $template_data = [
            'client_name' => $client_name,
            'client_email' => $client->user_email,
            'file_name' => $file->original_name,
            'file_size' => R35_Hub_File_Handler::format_file_size($file->file_size),
            'upload_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($file->created_at)),
            'admin_url' => $admin_url,
            'site_name' => $site_name,
        ];

        $message = $this->get_email_template('admin-new-file', $template_data);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->get_from_name() . ' <' . $this->get_from_email() . '>',
        ];

        $admin_email = $this->get_admin_email();
        return wp_mail($admin_email, $subject, $message, $headers);
    }

    /**
     * Get email template
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string
     */
    private function get_email_template($template, $data) {
        $template_path = R35_HUB_PLUGIN_DIR . 'templates/emails/' . $template . '.php';

        if (!file_exists($template_path)) {
            // Fallback to basic template
            return $this->get_fallback_template($template, $data);
        }

        // Extract data for use in template
        extract($data);

        ob_start();
        include $template_path;
        return ob_get_clean();
    }

    /**
     * Get fallback template if file doesn't exist
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string
     */
    private function get_fallback_template($template, $data) {
        $styles = $this->get_email_styles();

        if ($template === 'client-new-file') {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                {$styles}
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>{$data['site_name']}</h1>
                    </div>
                    <div class='content'>
                        <p>Hello {$data['client_name']},</p>
                        <p>A new file has been shared with you:</p>
                        <div class='file-info'>
                            <strong>{$data['file_name']}</strong><br>
                            <span class='meta'>Uploaded on {$data['upload_date']}</span>
                        </div>
                        <p><a href='{$data['dashboard_url']}' class='btn'>View My Files</a></p>
                        <p>Best regards,<br>{$data['site_name']}</p>
                    </div>
                </div>
            </body>
            </html>";
        }

        if ($template === 'admin-new-file') {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                {$styles}
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>{$data['site_name']}</h1>
                    </div>
                    <div class='content'>
                        <p>A new file has been uploaded by a client:</p>
                        <div class='file-info'>
                            <strong>Client:</strong> {$data['client_name']} ({$data['client_email']})<br>
                            <strong>File:</strong> {$data['file_name']}<br>
                            <strong>Size:</strong> {$data['file_size']}<br>
                            <strong>Uploaded:</strong> {$data['upload_date']}
                        </div>
                        <p><a href='{$data['admin_url']}' class='btn'>View in Dashboard</a></p>
                    </div>
                </div>
            </body>
            </html>";
        }

        return '';
    }

    /**
     * Get email styles
     *
     * @return string
     */
    private function get_email_styles() {
        return "
        <style>
            body {
                font-family: 'Helvetica Neue', Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: #fff;
                border: 1px solid #e0e0e0;
            }
            .header {
                background: #000;
                color: #fff;
                padding: 20px 30px;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 500;
            }
            .content {
                padding: 30px;
            }
            .file-info {
                background: #f9f9f9;
                border-left: 4px solid #000;
                padding: 15px;
                margin: 20px 0;
            }
            .meta {
                color: #666;
                font-size: 14px;
            }
            .btn {
                display: inline-block;
                background: #000;
                color: #fff !important;
                padding: 12px 24px;
                text-decoration: none;
                font-weight: 500;
            }
            .btn:hover {
                background: #333;
            }
        </style>";
    }

    /**
     * Get admin email address
     *
     * @return string
     */
    private function get_admin_email() {
        $settings = get_option('r35_hub_settings', []);
        return isset($settings['admin_email']) && !empty($settings['admin_email'])
            ? $settings['admin_email']
            : R35_HUB_ADMIN_EMAIL;
    }

    /**
     * Get from email address
     *
     * @return string
     */
    private function get_from_email() {
        return get_option('admin_email');
    }

    /**
     * Get from name
     *
     * @return string
     */
    private function get_from_name() {
        $settings = get_option('r35_hub_settings', []);
        return isset($settings['email_from_name']) && !empty($settings['email_from_name'])
            ? $settings['email_from_name']
            : get_bloginfo('name');
    }
}
