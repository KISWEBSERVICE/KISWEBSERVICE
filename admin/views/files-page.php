<?php
/**
 * Admin Files Management Page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap cfd-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="cfd-admin-container">
        <!-- Upload Section -->
        <div class="cfd-card">
            <h2><?php _e('Upload New File', 'client-file-dashboard'); ?></h2>

            <form id="cfd-upload-form" enctype="multipart/form-data">
                <div class="cfd-form-group">
                    <label for="cfd-file-input"><?php _e('Choose File', 'client-file-dashboard'); ?></label>
                    <input type="file" id="cfd-file-input" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif">
                    <p class="description"><?php _e('Allowed file types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF', 'client-file-dashboard'); ?></p>
                </div>

                <div class="cfd-form-group">
                    <label for="cfd-category-select"><?php _e('Category', 'client-file-dashboard'); ?></label>
                    <select id="cfd-category-select" name="category_id">
                        <option value="0"><?php _e('No Category', 'client-file-dashboard'); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->id); ?>">
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cfd-form-group">
                    <label>
                        <input type="checkbox" id="cfd-is-universal" name="is_universal" value="1">
                        <?php _e('Make this file visible to all users', 'client-file-dashboard'); ?>
                    </label>
                </div>

                <div class="cfd-form-group" id="cfd-user-assignment" style="display: none;">
                    <label><?php _e('Assign to Specific Users', 'client-file-dashboard'); ?></label>
                    <div class="cfd-user-select-wrapper">
                        <input type="text" id="cfd-user-search" placeholder="<?php _e('Search users...', 'client-file-dashboard'); ?>">
                        <div class="cfd-user-list">
                            <?php foreach ($users as $user): ?>
                                <label class="cfd-user-item">
                                    <input type="checkbox" name="assigned_users[]" value="<?php echo esc_attr($user->ID); ?>">
                                    <span><?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_email); ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="cfd-form-actions">
                    <button type="submit" class="button button-primary" id="cfd-upload-btn">
                        <?php _e('Upload File', 'client-file-dashboard'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>

                <div id="cfd-upload-message" class="cfd-message" style="display: none;"></div>
            </form>
        </div>

        <!-- Files List -->
        <div class="cfd-card">
            <h2><?php _e('Uploaded Files', 'client-file-dashboard'); ?></h2>

            <?php if (empty($files)): ?>
                <p class="cfd-no-files"><?php _e('No files uploaded yet.', 'client-file-dashboard'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped cfd-files-table">
                    <thead>
                        <tr>
                            <th><?php _e('File Name', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Category', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Type', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Size', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Visibility', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Uploaded', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Actions', 'client-file-dashboard'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr data-file-id="<?php echo esc_attr($file->id); ?>">
                                <td>
                                    <strong><?php echo esc_html($file->original_filename); ?></strong>
                                </td>
                                <td><?php echo $file->category_name ? esc_html($file->category_name) : '—'; ?></td>
                                <td><?php echo strtoupper(esc_html($file->file_type)); ?></td>
                                <td><?php echo size_format($file->file_size); ?></td>
                                <td>
                                    <?php if ($file->is_universal): ?>
                                        <span class="cfd-badge cfd-badge-universal"><?php _e('All Users', 'client-file-dashboard'); ?></span>
                                    <?php else: ?>
                                        <span class="cfd-badge cfd-badge-specific"><?php _e('Specific Users', 'client-file-dashboard'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($file->created_at))); ?></td>
                                <td>
                                    <button class="button button-small cfd-manage-assignments" data-file-id="<?php echo esc_attr($file->id); ?>">
                                        <?php _e('Manage Access', 'client-file-dashboard'); ?>
                                    </button>
                                    <button class="button button-small cfd-delete-file" data-file-id="<?php echo esc_attr($file->id); ?>">
                                        <?php _e('Delete', 'client-file-dashboard'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for managing file assignments -->
<div id="cfd-assignments-modal" class="cfd-modal" style="display: none;">
    <div class="cfd-modal-content">
        <span class="cfd-modal-close">&times;</span>
        <h2><?php _e('Manage File Access', 'client-file-dashboard'); ?></h2>

        <div class="cfd-modal-body">
            <div class="cfd-form-group">
                <label>
                    <input type="checkbox" id="cfd-modal-universal">
                    <?php _e('Make this file visible to all users', 'client-file-dashboard'); ?>
                </label>
            </div>

            <div id="cfd-modal-user-assignment">
                <label><?php _e('Assign to Specific Users', 'client-file-dashboard'); ?></label>
                <div class="cfd-user-select-wrapper">
                    <input type="text" id="cfd-modal-user-search" placeholder="<?php _e('Search users...', 'client-file-dashboard'); ?>">
                    <div class="cfd-user-list" id="cfd-modal-user-list">
                        <?php foreach ($users as $user): ?>
                            <label class="cfd-user-item">
                                <input type="checkbox" name="modal_assigned_users[]" value="<?php echo esc_attr($user->ID); ?>">
                                <span><?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_email); ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="cfd-modal-footer">
            <button type="button" class="button button-primary" id="cfd-save-assignments">
                <?php _e('Save Changes', 'client-file-dashboard'); ?>
            </button>
            <button type="button" class="button" id="cfd-cancel-assignments">
                <?php _e('Cancel', 'client-file-dashboard'); ?>
            </button>
        </div>
    </div>
</div>
