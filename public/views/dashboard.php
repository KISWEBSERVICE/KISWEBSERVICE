<?php
/**
 * Client Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cfd-dashboard-wrapper">
    <div class="cfd-dashboard-header">
        <h2><?php _e('My Files', 'client-file-dashboard'); ?></h2>
        <p class="cfd-welcome-text">
            <?php printf(__('Welcome, %s! Here you can view and download your files.', 'client-file-dashboard'), wp_get_current_user()->display_name); ?>
        </p>
    </div>

    <?php if (!empty($categories)): ?>
    <div class="cfd-filters">
        <label for="cfd-category-filter"><?php _e('Filter by Category:', 'client-file-dashboard'); ?></label>
        <select id="cfd-category-filter">
            <option value=""><?php _e('All Categories', 'client-file-dashboard'); ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo esc_attr($category->id); ?>">
                    <?php echo esc_html($category->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <div id="cfd-files-container">
        <?php if (empty($files)): ?>
            <div class="cfd-no-files">
                <div class="cfd-no-files-icon">📁</div>
                <p><?php _e('No files available at the moment.', 'client-file-dashboard'); ?></p>
            </div>
        <?php else: ?>
            <div class="cfd-files-grid">
                <?php foreach ($files as $file): ?>
                    <div class="cfd-file-card" data-file-id="<?php echo esc_attr($file->id); ?>" data-category-id="<?php echo esc_attr($file->category_id); ?>">
                        <div class="cfd-file-icon">
                            <?php
                            $icon = '📄';
                            $file_ext = strtolower($file->file_type);
                            if (in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif'))) {
                                $icon = '🖼️';
                            } elseif ($file_ext === 'pdf') {
                                $icon = '📕';
                            } elseif (in_array($file_ext, array('doc', 'docx'))) {
                                $icon = '📘';
                            } elseif (in_array($file_ext, array('xls', 'xlsx'))) {
                                $icon = '📗';
                            } elseif (in_array($file_ext, array('ppt', 'pptx'))) {
                                $icon = '📙';
                            }
                            echo $icon;
                            ?>
                        </div>

                        <div class="cfd-file-info">
                            <h3 class="cfd-file-name"><?php echo esc_html($file->original_filename); ?></h3>

                            <div class="cfd-file-meta">
                                <?php if ($file->category_name): ?>
                                    <span class="cfd-file-category">
                                        <span class="cfd-meta-icon">🏷️</span>
                                        <?php echo esc_html($file->category_name); ?>
                                    </span>
                                <?php endif; ?>

                                <span class="cfd-file-size">
                                    <span class="cfd-meta-icon">💾</span>
                                    <?php echo size_format($file->file_size); ?>
                                </span>

                                <span class="cfd-file-date">
                                    <span class="cfd-meta-icon">📅</span>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($file->created_at))); ?>
                                </span>
                            </div>

                            <?php if ($file->is_universal): ?>
                                <span class="cfd-file-badge cfd-badge-universal"><?php _e('Available to All', 'client-file-dashboard'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="cfd-file-actions">
                            <button class="cfd-download-btn" data-file-id="<?php echo esc_attr($file->id); ?>">
                                <span class="cfd-btn-icon">⬇️</span>
                                <?php _e('Download', 'client-file-dashboard'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="cfd-loading" class="cfd-loading" style="display: none;">
        <div class="cfd-spinner"></div>
        <p><?php _e('Loading files...', 'client-file-dashboard'); ?></p>
    </div>
</div>
