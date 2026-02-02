<?php
/**
 * Admin Settings Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

$defaults = [
    'max_file_size' => 24,
    'email_notifications' => true,
    'admin_email' => R35_HUB_ADMIN_EMAIL,
    'email_from_name' => get_bloginfo('name'),
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
];

$settings = wp_parse_args($settings, $defaults);
?>
<div class="wrap r35-hub-wrap">
    <h1><?php _e('Client Hub Settings', 'room35-client-hub'); ?></h1>

    <?php if (isset($saved) && $saved) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved successfully.', 'room35-client-hub'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('r35_hub_settings_nonce'); ?>

        <div class="r35-settings-section">
            <h2><?php _e('File Settings', 'room35-client-hub'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="max_file_size"><?php _e('Maximum File Size (MB)', 'room35-client-hub'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="max_file_size" id="max_file_size"
                            value="<?php echo esc_attr($settings['max_file_size']); ?>"
                            min="1" max="100" class="small-text">
                        <p class="description"><?php _e('Maximum file size in megabytes that can be uploaded.', 'room35-client-hub'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="allowed_extensions"><?php _e('Allowed File Types', 'room35-client-hub'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="allowed_extensions" id="allowed_extensions"
                            value="<?php echo esc_attr(implode(',', $settings['allowed_extensions'])); ?>"
                            class="regular-text">
                        <p class="description"><?php _e('Comma-separated list of allowed file extensions (e.g., pdf,doc,docx,jpg,png)', 'room35-client-hub'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="r35-settings-section">
            <h2><?php _e('Email Notifications', 'room35-client-hub'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Notifications', 'room35-client-hub'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="email_notifications" value="1"
                                <?php checked($settings['email_notifications'], true); ?>>
                            <?php _e('Send email notifications when files are uploaded', 'room35-client-hub'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="admin_email"><?php _e('Admin Email', 'room35-client-hub'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="admin_email" id="admin_email"
                            value="<?php echo esc_attr($settings['admin_email']); ?>"
                            class="regular-text">
                        <p class="description"><?php _e('Email address to receive notifications when clients upload files.', 'room35-client-hub'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="email_from_name"><?php _e('Email From Name', 'room35-client-hub'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="email_from_name" id="email_from_name"
                            value="<?php echo esc_attr($settings['email_from_name']); ?>"
                            class="regular-text">
                        <p class="description"><?php _e('Name shown in the "From" field of notification emails.', 'room35-client-hub'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="r35-settings-section">
            <h2><?php _e('Dashboard Page', 'room35-client-hub'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Client Dashboard Page', 'room35-client-hub'); ?></th>
                    <td>
                        <?php if ($dashboard_page_id && get_post($dashboard_page_id)) : ?>
                            <p>
                                <a href="<?php echo get_permalink($dashboard_page_id); ?>" target="_blank">
                                    <?php echo esc_html(get_the_title($dashboard_page_id)); ?>
                                </a>
                                <a href="<?php echo get_edit_post_link($dashboard_page_id); ?>" class="button button-small">
                                    <?php _e('Edit Page', 'room35-client-hub'); ?>
                                </a>
                            </p>
                            <p class="description"><?php _e('This page contains the [room35_client_hub] shortcode.', 'room35-client-hub'); ?></p>
                        <?php else : ?>
                            <p class="description"><?php _e('No dashboard page found. Create a page with the shortcode [room35_client_hub]', 'room35-client-hub'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Shortcode', 'room35-client-hub'); ?></th>
                    <td>
                        <code>[room35_client_hub]</code>
                        <p class="description"><?php _e('Use this shortcode on any page to display the client dashboard.', 'room35-client-hub'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <input type="submit" name="r35_hub_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'room35-client-hub'); ?>">
        </p>
    </form>
</div>
