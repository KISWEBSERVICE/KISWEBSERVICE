<?php
/**
 * Admin Categories Management Page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap cfd-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="cfd-admin-container">
        <!-- Create Category Section -->
        <div class="cfd-card">
            <h2><?php _e('Create New Category', 'client-file-dashboard'); ?></h2>

            <form id="cfd-category-form">
                <div class="cfd-form-group">
                    <label for="cfd-category-name"><?php _e('Category Name', 'client-file-dashboard'); ?></label>
                    <input type="text" id="cfd-category-name" name="name" required class="regular-text">
                </div>

                <div class="cfd-form-group">
                    <label for="cfd-category-description"><?php _e('Description', 'client-file-dashboard'); ?></label>
                    <textarea id="cfd-category-description" name="description" rows="3" class="large-text"></textarea>
                </div>

                <div class="cfd-form-actions">
                    <button type="submit" class="button button-primary" id="cfd-create-category-btn">
                        <?php _e('Create Category', 'client-file-dashboard'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>

                <div id="cfd-category-message" class="cfd-message" style="display: none;"></div>
            </form>
        </div>

        <!-- Categories List -->
        <div class="cfd-card">
            <h2><?php _e('Categories', 'client-file-dashboard'); ?></h2>

            <?php if (empty($categories)): ?>
                <p class="cfd-no-categories"><?php _e('No categories created yet.', 'client-file-dashboard'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped cfd-categories-table">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Description', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Created', 'client-file-dashboard'); ?></th>
                            <th><?php _e('Actions', 'client-file-dashboard'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr data-category-id="<?php echo esc_attr($category->id); ?>">
                                <td><strong><?php echo esc_html($category->name); ?></strong></td>
                                <td><?php echo esc_html($category->description); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($category->created_at))); ?></td>
                                <td>
                                    <button class="button button-small cfd-delete-category" data-category-id="<?php echo esc_attr($category->id); ?>">
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
