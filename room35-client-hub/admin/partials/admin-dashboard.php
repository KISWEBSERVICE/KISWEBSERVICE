<?php
/**
 * Admin Dashboard Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap r35-hub-wrap">
    <h1><?php _e('Client Hub Dashboard', 'room35-client-hub'); ?></h1>

    <div class="r35-dashboard-stats">
        <div class="r35-stat-card">
            <div class="r35-stat-icon">
                <span class="dashicons dashicons-media-document"></span>
            </div>
            <div class="r35-stat-content">
                <div class="r35-stat-number"><?php echo esc_html($total_files); ?></div>
                <div class="r35-stat-label"><?php _e('Total Files', 'room35-client-hub'); ?></div>
            </div>
        </div>

        <div class="r35-stat-card">
            <div class="r35-stat-icon">
                <span class="dashicons dashicons-admin-users"></span>
            </div>
            <div class="r35-stat-content">
                <div class="r35-stat-number"><?php echo esc_html($total_clients); ?></div>
                <div class="r35-stat-label"><?php _e('Total Clients', 'room35-client-hub'); ?></div>
            </div>
        </div>

        <div class="r35-stat-card">
            <div class="r35-stat-icon">
                <span class="dashicons dashicons-upload"></span>
            </div>
            <div class="r35-stat-content">
                <div class="r35-stat-number"><?php echo esc_html($admin_files); ?></div>
                <div class="r35-stat-label"><?php _e('Admin Uploads', 'room35-client-hub'); ?></div>
            </div>
        </div>

        <div class="r35-stat-card">
            <div class="r35-stat-icon">
                <span class="dashicons dashicons-download"></span>
            </div>
            <div class="r35-stat-content">
                <div class="r35-stat-number"><?php echo esc_html($client_files); ?></div>
                <div class="r35-stat-label"><?php _e('Client Uploads', 'room35-client-hub'); ?></div>
            </div>
        </div>

        <div class="r35-stat-card">
            <div class="r35-stat-icon">
                <span class="dashicons dashicons-admin-site"></span>
            </div>
            <div class="r35-stat-content">
                <div class="r35-stat-number"><?php echo esc_html($global_files); ?></div>
                <div class="r35-stat-label"><?php _e('Global Files', 'room35-client-hub'); ?></div>
            </div>
        </div>
    </div>

    <div class="r35-dashboard-actions">
        <a href="<?php echo admin_url('admin.php?page=r35-hub-upload'); ?>" class="button button-primary button-hero">
            <span class="dashicons dashicons-upload"></span>
            <?php _e('Upload New File', 'room35-client-hub'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=r35-hub-files'); ?>" class="button button-hero">
            <span class="dashicons dashicons-list-view"></span>
            <?php _e('View All Files', 'room35-client-hub'); ?>
        </a>
    </div>

    <div class="r35-dashboard-section">
        <h2><?php _e('Recent Files', 'room35-client-hub'); ?></h2>

        <?php if (empty($recent_files)) : ?>
            <p class="r35-no-files"><?php _e('No files uploaded yet.', 'room35-client-hub'); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('File Name', 'room35-client-hub'); ?></th>
                        <th><?php _e('Client', 'room35-client-hub'); ?></th>
                        <th><?php _e('Source', 'room35-client-hub'); ?></th>
                        <th><?php _e('Date', 'room35-client-hub'); ?></th>
                        <th><?php _e('Actions', 'room35-client-hub'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_files as $file) :
                        $file_handler = new R35_Hub_File_Handler();
                        $view_url = $file_handler->get_secure_download_url($file->id, 'view');
                        $download_url = $file_handler->get_secure_download_url($file->id, 'download');
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($file->original_name); ?></strong>
                                <br>
                                <small><?php echo R35_Hub_File_Handler::format_file_size($file->file_size); ?></small>
                            </td>
                            <td>
                                <?php if ($file->is_global) : ?>
                                    <span class="r35-badge r35-badge-global"><?php _e('Global', 'room35-client-hub'); ?></span>
                                <?php elseif ($file->assigned_to) : ?>
                                    <?php echo esc_html(R35_Hub_User_Role::get_user_display_name($file->assigned_to)); ?>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($file->upload_source === 'admin') : ?>
                                    <span class="r35-badge r35-badge-admin"><?php _e('Admin', 'room35-client-hub'); ?></span>
                                <?php else : ?>
                                    <span class="r35-badge r35-badge-client"><?php _e('Client', 'room35-client-hub'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format'), strtotime($file->created_at)); ?></td>
                            <td>
                                <a href="<?php echo esc_url($view_url); ?>" class="button button-small" target="_blank">
                                    <?php _e('View', 'room35-client-hub'); ?>
                                </a>
                                <a href="<?php echo esc_url($download_url); ?>" class="button button-small">
                                    <?php _e('Download', 'room35-client-hub'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
