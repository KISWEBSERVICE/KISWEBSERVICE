<?php
/**
 * Admin Upload Form Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('r35_hub_settings', []);
$max_size = isset($settings['max_file_size']) ? $settings['max_file_size'] : 24;
?>
<div class="wrap r35-hub-wrap">
    <h1><?php _e('Upload File', 'room35-client-hub'); ?></h1>

    <div class="r35-upload-container">
        <form id="r35-admin-upload-form" enctype="multipart/form-data">
            <?php wp_nonce_field('r35_hub_nonce', 'nonce'); ?>

            <div class="r35-upload-section">
                <h2><?php _e('Select File', 'room35-client-hub'); ?></h2>

                <div class="r35-dropzone" id="r35-dropzone">
                    <input type="file" name="file" id="r35-file-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <div class="r35-dropzone-content">
                        <span class="dashicons dashicons-cloud-upload"></span>
                        <p><?php _e('Drag and drop a file here, or click to select', 'room35-client-hub'); ?></p>
                        <p class="r35-file-types"><?php _e('Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG', 'room35-client-hub'); ?></p>
                        <p class="r35-file-size"><?php printf(__('Maximum size: %dMB', 'room35-client-hub'), $max_size); ?></p>
                    </div>
                    <div class="r35-file-preview" style="display: none;">
                        <span class="dashicons dashicons-media-document"></span>
                        <span class="r35-file-name"></span>
                        <button type="button" class="r35-remove-file">&times;</button>
                    </div>
                </div>
            </div>

            <div class="r35-upload-section">
                <h2><?php _e('Assign To', 'room35-client-hub'); ?></h2>

                <div class="r35-assign-options">
                    <label class="r35-radio-card">
                        <input type="radio" name="assign_type" value="client" checked>
                        <span class="r35-radio-card-content">
                            <span class="dashicons dashicons-admin-users"></span>
                            <span class="r35-radio-label"><?php _e('Specific Client', 'room35-client-hub'); ?></span>
                        </span>
                    </label>

                    <label class="r35-radio-card">
                        <input type="radio" name="assign_type" value="global">
                        <span class="r35-radio-card-content">
                            <span class="dashicons dashicons-admin-site"></span>
                            <span class="r35-radio-label"><?php _e('All Clients (Global)', 'room35-client-hub'); ?></span>
                        </span>
                    </label>
                </div>

                <div class="r35-client-select" id="r35-client-select">
                    <label for="assigned_to"><?php _e('Select Client:', 'room35-client-hub'); ?></label>
                    <select name="assigned_to" id="assigned_to">
                        <option value=""><?php _e('— Select a client —', 'room35-client-hub'); ?></option>
                        <?php foreach ($clients as $client) : ?>
                            <option value="<?php echo esc_attr($client->ID); ?>">
                                <?php echo esc_html(R35_Hub_User_Role::get_user_display_name($client->ID)); ?>
                                (<?php echo esc_html($client->user_email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="r35-upload-actions">
                <button type="submit" class="button button-primary button-hero" id="r35-upload-btn">
                    <span class="dashicons dashicons-upload"></span>
                    <?php _e('Upload File', 'room35-client-hub'); ?>
                </button>
            </div>

            <div class="r35-upload-progress" style="display: none;">
                <div class="r35-progress-bar">
                    <div class="r35-progress-fill"></div>
                </div>
                <p class="r35-progress-text"><?php _e('Uploading...', 'room35-client-hub'); ?></p>
            </div>

            <div class="r35-upload-result" style="display: none;"></div>
        </form>
    </div>
</div>
