<?php
/**
 * Client Dashboard Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

$display_name = R35_Hub_User_Role::get_user_display_name($user_id);
$settings = get_option('r35_hub_settings', []);
$max_size = isset($settings['max_file_size']) ? $settings['max_file_size'] : 24;
?>
<div class="r35-client-hub">
    <!-- Header -->
    <div class="r35-hub-header">
        <div class="r35-hub-welcome">
            <h1><?php _e('My Dashboard', 'room35-client-hub'); ?></h1>
            <p class="r35-welcome-text">
                <?php printf(__('Welcome back, %s', 'room35-client-hub'), esc_html($display_name)); ?>
            </p>
        </div>
        <div class="r35-hub-user">
            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="r35-btn r35-btn-outline r35-btn-sm">
                <?php _e('Log Out', 'room35-client-hub'); ?>
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="r35-hub-tabs">
        <button class="r35-tab-btn active" data-tab="my-files">
            <?php _e('My Files', 'room35-client-hub'); ?>
            <?php if (count($my_files) > 0) : ?>
                <span class="r35-tab-count"><?php echo count($my_files); ?></span>
            <?php endif; ?>
        </button>
        <?php if (!empty($global_files)) : ?>
            <button class="r35-tab-btn" data-tab="global-files">
                <?php _e('General Documents', 'room35-client-hub'); ?>
                <span class="r35-tab-count"><?php echo count($global_files); ?></span>
            </button>
        <?php endif; ?>
        <?php if ($show_upload) : ?>
            <button class="r35-tab-btn" data-tab="upload">
                <?php _e('Upload File', 'room35-client-hub'); ?>
            </button>
        <?php endif; ?>
        <button class="r35-tab-btn" data-tab="my-uploads">
            <?php _e('My Uploads', 'room35-client-hub'); ?>
            <?php if (count($my_uploads) > 0) : ?>
                <span class="r35-tab-count"><?php echo count($my_uploads); ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Tab Content -->
    <div class="r35-hub-content">
        <!-- My Files Tab -->
        <div class="r35-tab-content active" id="tab-my-files">
            <div class="r35-section-header">
                <h2><?php _e('Files Shared With You', 'room35-client-hub'); ?></h2>
                <p class="r35-section-desc"><?php _e('Documents that have been shared with you by our team.', 'room35-client-hub'); ?></p>
            </div>

            <?php if (empty($my_files)) : ?>
                <div class="r35-empty-state">
                    <div class="r35-empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <p><?php _e('No files have been shared with you yet.', 'room35-client-hub'); ?></p>
                </div>
            <?php else : ?>
                <div class="r35-file-grid">
                    <?php foreach ($my_files as $file) : ?>
                        <?php include R35_HUB_PLUGIN_DIR . 'public/partials/file-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Global Files Tab -->
        <?php if (!empty($global_files)) : ?>
            <div class="r35-tab-content" id="tab-global-files">
                <div class="r35-section-header">
                    <h2><?php _e('General Documents', 'room35-client-hub'); ?></h2>
                    <p class="r35-section-desc"><?php _e('Documents available to all clients.', 'room35-client-hub'); ?></p>
                </div>

                <div class="r35-file-grid">
                    <?php foreach ($global_files as $file) : ?>
                        <?php include R35_HUB_PLUGIN_DIR . 'public/partials/file-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Upload Tab -->
        <?php if ($show_upload) : ?>
            <div class="r35-tab-content" id="tab-upload">
                <div class="r35-section-header">
                    <h2><?php _e('Upload a File', 'room35-client-hub'); ?></h2>
                    <p class="r35-section-desc"><?php _e('Submit documents such as signed contracts, financial statements, or other requested files.', 'room35-client-hub'); ?></p>
                </div>

                <form id="r35-client-upload-form" class="r35-upload-form" enctype="multipart/form-data">
                    <?php wp_nonce_field('r35_hub_client_nonce', 'nonce'); ?>

                    <div class="r35-dropzone" id="r35-client-dropzone">
                        <input type="file" name="file" id="r35-client-file-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <div class="r35-dropzone-content">
                            <div class="r35-dropzone-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <p class="r35-dropzone-text"><?php _e('Drag and drop a file here, or click to select', 'room35-client-hub'); ?></p>
                            <p class="r35-dropzone-hint"><?php _e('PDF, DOC, DOCX, XLS, XLSX, JPG, PNG', 'room35-client-hub'); ?></p>
                            <p class="r35-dropzone-hint"><?php printf(__('Maximum file size: %dMB', 'room35-client-hub'), $max_size); ?></p>
                        </div>
                        <div class="r35-file-selected" style="display: none;">
                            <div class="r35-selected-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                            </div>
                            <span class="r35-selected-name"></span>
                            <button type="button" class="r35-remove-file">&times;</button>
                        </div>
                    </div>

                    <div class="r35-upload-actions">
                        <button type="submit" class="r35-btn r35-btn-primary" id="r35-client-upload-btn">
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
        <?php endif; ?>

        <!-- My Uploads Tab -->
        <div class="r35-tab-content" id="tab-my-uploads">
            <div class="r35-section-header">
                <h2><?php _e('Files You\'ve Uploaded', 'room35-client-hub'); ?></h2>
                <p class="r35-section-desc"><?php _e('Documents you have submitted to us.', 'room35-client-hub'); ?></p>
            </div>

            <?php if (empty($my_uploads)) : ?>
                <div class="r35-empty-state">
                    <div class="r35-empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <p><?php _e('You haven\'t uploaded any files yet.', 'room35-client-hub'); ?></p>
                </div>
            <?php else : ?>
                <div class="r35-file-grid">
                    <?php foreach ($my_uploads as $file) : ?>
                        <?php include R35_HUB_PLUGIN_DIR . 'public/partials/file-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
