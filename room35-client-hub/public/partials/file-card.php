<?php
/**
 * File Card Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

$file_handler = new R35_Hub_File_Handler();
$view_url = $file_handler->get_secure_download_url($file->id, 'view');
$download_url = $file_handler->get_secure_download_url($file->id, 'download');
$file_icon = R35_Hub_File_Handler::get_file_icon($file->file_type);
$file_ext = strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION));
$file_date = date_i18n(get_option('date_format'), strtotime($file->created_at));
?>
<div class="r35-file-card">
    <div class="r35-file-icon r35-file-icon-<?php echo esc_attr($file_icon); ?>">
        <?php if ($file_icon === 'pdf') : ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        <?php elseif ($file_icon === 'doc') : ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <line x1="10" y1="9" x2="8" y2="9"></line>
            </svg>
        <?php elseif ($file_icon === 'xls') : ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <rect x="8" y="12" width="8" height="6" rx="1"></rect>
            </svg>
        <?php elseif ($file_icon === 'image') : ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
        <?php else : ?>
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        <?php endif; ?>
        <span class="r35-file-ext"><?php echo esc_html($file_ext); ?></span>
    </div>

    <div class="r35-file-info">
        <h3 class="r35-file-name" title="<?php echo esc_attr($file->original_name); ?>">
            <?php echo esc_html($file->original_name); ?>
        </h3>
        <p class="r35-file-meta">
            <?php echo esc_html(R35_Hub_File_Handler::format_file_size($file->file_size)); ?>
            <span class="r35-meta-sep">•</span>
            <?php echo esc_html($file_date); ?>
        </p>
    </div>

    <div class="r35-file-actions">
        <a href="<?php echo esc_url($view_url); ?>" class="r35-btn r35-btn-sm r35-btn-outline" target="_blank" title="<?php esc_attr_e('View', 'room35-client-hub'); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <span><?php _e('View', 'room35-client-hub'); ?></span>
        </a>
        <a href="<?php echo esc_url($download_url); ?>" class="r35-btn r35-btn-sm r35-btn-primary" title="<?php esc_attr_e('Download', 'room35-client-hub'); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <span><?php _e('Download', 'room35-client-hub'); ?></span>
        </a>
    </div>
</div>
