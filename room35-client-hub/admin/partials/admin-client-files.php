<?php
/**
 * Admin Client Files Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap r35-hub-wrap">
    <h1><?php _e('Client Files', 'room35-client-hub'); ?></h1>

    <div class="r35-client-selector">
        <form method="get">
            <input type="hidden" name="page" value="r35-hub-client-files">
            <label for="client_id"><?php _e('Select Client:', 'room35-client-hub'); ?></label>
            <select name="client_id" id="client_id" onchange="this.form.submit()">
                <option value=""><?php _e('— Select a client —', 'room35-client-hub'); ?></option>
                <?php foreach ($clients as $c) : ?>
                    <option value="<?php echo esc_attr($c->ID); ?>" <?php selected($selected_client, $c->ID); ?>>
                        <?php echo esc_html(R35_Hub_User_Role::get_user_display_name($c->ID)); ?>
                        (<?php echo esc_html($c->user_email); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($selected_client && $client) : ?>
        <div class="r35-client-info">
            <h2>
                <?php echo esc_html(R35_Hub_User_Role::get_user_display_name($selected_client)); ?>
                <small>(<?php echo esc_html($client->user_email); ?>)</small>
            </h2>

            <a href="<?php echo admin_url('admin.php?page=r35-hub-upload'); ?>" class="button button-primary">
                <?php _e('Upload File for This Client', 'room35-client-hub'); ?>
            </a>
        </div>

        <div class="r35-client-files-section">
            <h3><?php _e('Files Shared With Client', 'room35-client-hub'); ?></h3>

            <?php if (empty($client_files)) : ?>
                <p class="r35-no-files"><?php _e('No files have been shared with this client yet.', 'room35-client-hub'); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('File Name', 'room35-client-hub'); ?></th>
                            <th><?php _e('Size', 'room35-client-hub'); ?></th>
                            <th><?php _e('Date', 'room35-client-hub'); ?></th>
                            <th><?php _e('Downloads', 'room35-client-hub'); ?></th>
                            <th><?php _e('Actions', 'room35-client-hub'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($client_files as $file) :
                            $view_url = $file_handler->get_secure_download_url($file->id, 'view');
                            $download_url = $file_handler->get_secure_download_url($file->id, 'download');
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($file->original_name); ?></strong></td>
                                <td><?php echo R35_Hub_File_Handler::format_file_size($file->file_size); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($file->created_at)); ?></td>
                                <td><?php echo esc_html($file->download_count); ?></td>
                                <td>
                                    <a href="<?php echo esc_url($view_url); ?>" class="button button-small" target="_blank"><?php _e('View', 'room35-client-hub'); ?></a>
                                    <a href="<?php echo esc_url($download_url); ?>" class="button button-small"><?php _e('Download', 'room35-client-hub'); ?></a>
                                    <button type="button" class="button button-small r35-delete-file" data-file-id="<?php echo esc_attr($file->id); ?>"><?php _e('Delete', 'room35-client-hub'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="r35-client-files-section">
            <h3><?php _e('Files Uploaded By Client', 'room35-client-hub'); ?></h3>

            <?php if (empty($client_uploads)) : ?>
                <p class="r35-no-files"><?php _e('This client has not uploaded any files yet.', 'room35-client-hub'); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('File Name', 'room35-client-hub'); ?></th>
                            <th><?php _e('Size', 'room35-client-hub'); ?></th>
                            <th><?php _e('Date', 'room35-client-hub'); ?></th>
                            <th><?php _e('Actions', 'room35-client-hub'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($client_uploads as $file) :
                            $view_url = $file_handler->get_secure_download_url($file->id, 'view');
                            $download_url = $file_handler->get_secure_download_url($file->id, 'download');
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($file->original_name); ?></strong></td>
                                <td><?php echo R35_Hub_File_Handler::format_file_size($file->file_size); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($file->created_at)); ?></td>
                                <td>
                                    <a href="<?php echo esc_url($view_url); ?>" class="button button-small" target="_blank"><?php _e('View', 'room35-client-hub'); ?></a>
                                    <a href="<?php echo esc_url($download_url); ?>" class="button button-small"><?php _e('Download', 'room35-client-hub'); ?></a>
                                    <button type="button" class="button button-small r35-delete-file" data-file-id="<?php echo esc_attr($file->id); ?>"><?php _e('Delete', 'room35-client-hub'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    <?php else : ?>
        <p class="r35-select-prompt"><?php _e('Please select a client from the dropdown above to view their files.', 'room35-client-hub'); ?></p>
    <?php endif; ?>
</div>
