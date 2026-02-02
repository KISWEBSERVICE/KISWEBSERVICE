<?php
/**
 * Admin File List Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap r35-hub-wrap">
    <h1 class="wp-heading-inline"><?php _e('All Files', 'room35-client-hub'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=r35-hub-upload'); ?>" class="page-title-action">
        <?php _e('Upload New File', 'room35-client-hub'); ?>
    </a>
    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="r35-hub-files">
        <?php
        $list_table->search_box(__('Search Files', 'room35-client-hub'), 'file-search');
        $list_table->display();
        ?>
    </form>
</div>
