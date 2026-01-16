<?php
/**
 * Admin Dashboard Template
 * Displays gift cards management interface
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Gift Cards', 'smittys-gift-cards'); ?></h1>

    <!-- Statistics -->
    <div class="smittys-gc-stats">
        <div class="stat-box">
            <div class="stat-number"><?php echo esc_html($total_cards); ?></div>
            <div class="stat-label"><?php _e('Total Gift Cards', 'smittys-gift-cards'); ?></div>
        </div>
        <div class="stat-box stat-active">
            <div class="stat-number"><?php echo esc_html($active_cards); ?></div>
            <div class="stat-label"><?php _e('Active', 'smittys-gift-cards'); ?></div>
        </div>
        <div class="stat-box stat-redeemed">
            <div class="stat-number"><?php echo esc_html($redeemed_cards); ?></div>
            <div class="stat-label"><?php _e('Redeemed', 'smittys-gift-cards'); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="smittys-gc-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="smittys-gift-cards" />

            <div class="filter-row">
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search recipient, email, or product...', 'smittys-gift-cards'); ?>" class="search-input" />

                <select name="status">
                    <option value=""><?php _e('All Status', 'smittys-gift-cards'); ?></option>
                    <option value="active" <?php selected($status_filter, 'active'); ?>><?php _e('Active', 'smittys-gift-cards'); ?></option>
                    <option value="redeemed" <?php selected($status_filter, 'redeemed'); ?>><?php _e('Redeemed', 'smittys-gift-cards'); ?></option>
                </select>

                <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" placeholder="<?php _e('From Date', 'smittys-gift-cards'); ?>" />
                <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" placeholder="<?php _e('To Date', 'smittys-gift-cards'); ?>" />

                <button type="submit" class="button"><?php _e('Filter', 'smittys-gift-cards'); ?></button>

                <?php if ($search || $status_filter || $date_from || $date_to): ?>
                    <a href="<?php echo admin_url('admin.php?page=smittys-gift-cards'); ?>" class="button"><?php _e('Clear', 'smittys-gift-cards'); ?></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Gift Cards Table -->
    <?php if (empty($gift_cards)): ?>
        <div class="smittys-gc-empty">
            <p><?php _e('No gift cards found.', 'smittys-gift-cards'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'smittys-gift-cards'); ?></th>
                    <th><?php _e('Recipient', 'smittys-gift-cards'); ?></th>
                    <th><?php _e('Product/Variation', 'smittys-gift-cards'); ?></th>
                    <th><?php _e('Purchase Date', 'smittys-gift-cards'); ?></th>
                    <th><?php _e('Expiration', 'smittys-gift-cards'); ?></th>
                    <th><?php _e('Status', 'smittys-gift-cards'); ?></th>
                    <th><?php _e('Actions', 'smittys-gift-cards'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gift_cards as $card): ?>
                    <tr data-gift-card-id="<?php echo esc_attr($card->id); ?>">
                        <td><?php echo esc_html($card->id); ?></td>
                        <td>
                            <strong><?php echo esc_html($card->recipient_name); ?></strong><br>
                            <a href="mailto:<?php echo esc_attr($card->recipient_email); ?>"><?php echo esc_html($card->recipient_email); ?></a>
                            <?php if ($card->custom_message): ?>
                                <br><em class="description"><?php echo esc_html(wp_trim_words($card->custom_message, 10)); ?></em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html($card->product_variation_name); ?>
                            <br>
                            <a href="<?php echo admin_url('post.php?post=' . $card->order_id . '&action=edit'); ?>" target="_blank">
                                <?php printf(__('Order #%d', 'smittys-gift-cards'), $card->order_id); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo date('M j, Y', strtotime($card->purchase_date)); ?>
                        </td>
                        <td>
                            <?php if ($card->expiration_date): ?>
                                <?php
                                $expired = strtotime($card->expiration_date) < time();
                                $class = $expired ? 'expired' : '';
                                ?>
                                <span class="<?php echo $class; ?>">
                                    <?php echo date('M j, Y', strtotime($card->expiration_date)); ?>
                                    <?php if ($expired): ?>
                                        <br><span style="color: red;"><?php _e('(Expired)', 'smittys-gift-cards'); ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <em><?php _e('No expiration', 'smittys-gift-cards'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($card->is_redeemed): ?>
                                <span class="status-badge status-redeemed"><?php _e('Redeemed', 'smittys-gift-cards'); ?></span>
                                <?php if ($card->redeemed_date): ?>
                                    <br><small><?php echo date('M j, Y', strtotime($card->redeemed_date)); ?></small>
                                    <?php if ($card->redeemed_by): ?>
                                        <br><small><?php printf(__('by %s', 'smittys-gift-cards'), esc_html($card->redeemed_by)); ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="status-badge status-active"><?php _e('Active', 'smittys-gift-cards'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="button button-small toggle-redemption"
                                    data-gift-card-id="<?php echo esc_attr($card->id); ?>"
                                    data-is-redeemed="<?php echo $card->is_redeemed ? '0' : '1'; ?>">
                                <?php echo $card->is_redeemed ? __('Mark Active', 'smittys-gift-cards') : __('Mark Redeemed', 'smittys-gift-cards'); ?>
                            </button>
                            <button class="button button-small resend-email"
                                    data-gift-card-id="<?php echo esc_attr($card->id); ?>">
                                <?php _e('Resend Email', 'smittys-gift-cards'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
