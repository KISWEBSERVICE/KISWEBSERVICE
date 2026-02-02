<?php
/**
 * Admin File List Table
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class R35_Hub_Admin_File_List extends WP_List_Table {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct([
            'singular' => __('File', 'room35-client-hub'),
            'plural' => __('Files', 'room35-client-hub'),
            'ajax' => false,
        ]);
    }

    /**
     * Get table columns
     *
     * @return array
     */
    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'file_name' => __('File Name', 'room35-client-hub'),
            'file_type' => __('Type', 'room35-client-hub'),
            'file_size' => __('Size', 'room35-client-hub'),
            'assigned_to' => __('Client', 'room35-client-hub'),
            'uploaded_by' => __('Uploaded By', 'room35-client-hub'),
            'upload_source' => __('Source', 'room35-client-hub'),
            'created_at' => __('Date', 'room35-client-hub'),
            'actions' => __('Actions', 'room35-client-hub'),
        ];
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        return [
            'file_name' => ['file_name', false],
            'file_size' => ['file_size', false],
            'assigned_to' => ['assigned_to', false],
            'created_at' => ['created_at', true],
        ];
    }

    /**
     * Checkbox column
     *
     * @param object $item
     * @return string
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="file_ids[]" value="%d" />',
            $item->id
        );
    }

    /**
     * File name column
     *
     * @param object $item
     * @return string
     */
    public function column_file_name($item) {
        $file_handler = new R35_Hub_File_Handler();
        $view_url = $file_handler->get_secure_download_url($item->id, 'view');

        return sprintf(
            '<strong><a href="%s" target="_blank">%s</a></strong>',
            esc_url($view_url),
            esc_html($item->original_name)
        );
    }

    /**
     * File type column
     *
     * @param object $item
     * @return string
     */
    public function column_file_type($item) {
        $icon = R35_Hub_File_Handler::get_file_icon($item->file_type);
        $ext = strtoupper(pathinfo($item->original_name, PATHINFO_EXTENSION));

        return sprintf(
            '<span class="r35-file-type r35-file-type-%s">%s</span>',
            esc_attr($icon),
            esc_html($ext)
        );
    }

    /**
     * File size column
     *
     * @param object $item
     * @return string
     */
    public function column_file_size($item) {
        return R35_Hub_File_Handler::format_file_size($item->file_size);
    }

    /**
     * Assigned to column
     *
     * @param object $item
     * @return string
     */
    public function column_assigned_to($item) {
        if ($item->is_global) {
            return '<span class="r35-badge r35-badge-global">' . __('Global', 'room35-client-hub') . '</span>';
        }

        if ($item->assigned_to) {
            return esc_html(R35_Hub_User_Role::get_user_display_name($item->assigned_to));
        }

        return '—';
    }

    /**
     * Uploaded by column
     *
     * @param object $item
     * @return string
     */
    public function column_uploaded_by($item) {
        return esc_html(R35_Hub_User_Role::get_user_display_name($item->uploaded_by));
    }

    /**
     * Upload source column
     *
     * @param object $item
     * @return string
     */
    public function column_upload_source($item) {
        if ($item->upload_source === 'admin') {
            return '<span class="r35-badge r35-badge-admin">' . __('Admin', 'room35-client-hub') . '</span>';
        }
        return '<span class="r35-badge r35-badge-client">' . __('Client', 'room35-client-hub') . '</span>';
    }

    /**
     * Date column
     *
     * @param object $item
     * @return string
     */
    public function column_created_at($item) {
        return date_i18n(
            get_option('date_format') . ' ' . get_option('time_format'),
            strtotime($item->created_at)
        );
    }

    /**
     * Actions column
     *
     * @param object $item
     * @return string
     */
    public function column_actions($item) {
        $file_handler = new R35_Hub_File_Handler();
        $view_url = $file_handler->get_secure_download_url($item->id, 'view');
        $download_url = $file_handler->get_secure_download_url($item->id, 'download');

        $actions = sprintf(
            '<a href="%s" class="button button-small" target="_blank" title="%s"><span class="dashicons dashicons-visibility"></span></a>',
            esc_url($view_url),
            esc_attr__('View', 'room35-client-hub')
        );

        $actions .= sprintf(
            ' <a href="%s" class="button button-small" title="%s"><span class="dashicons dashicons-download"></span></a>',
            esc_url($download_url),
            esc_attr__('Download', 'room35-client-hub')
        );

        $actions .= sprintf(
            ' <button type="button" class="button button-small r35-delete-file" data-file-id="%d" title="%s"><span class="dashicons dashicons-trash"></span></button>',
            $item->id,
            esc_attr__('Delete', 'room35-client-hub')
        );

        return $actions;
    }

    /**
     * Get bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        return [
            'delete' => __('Delete', 'room35-client-hub'),
        ];
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
                wp_die(__('Security check failed.', 'room35-client-hub'));
            }

            $file_ids = isset($_REQUEST['file_ids']) ? array_map('absint', $_REQUEST['file_ids']) : [];

            if (!empty($file_ids)) {
                $file_handler = new R35_Hub_File_Handler();
                foreach ($file_ids as $file_id) {
                    $file_handler->delete_file($file_id);
                }
            }
        }
    }

    /**
     * Extra table navigation (filters)
     *
     * @param string $which Top or bottom
     */
    public function extra_tablenav($which) {
        if ($which !== 'top') {
            return;
        }

        $clients = R35_Hub_User_Role::get_assignable_users();
        $selected_client = isset($_GET['filter_client']) ? absint($_GET['filter_client']) : 0;
        $selected_source = isset($_GET['filter_source']) ? sanitize_text_field($_GET['filter_source']) : '';

        echo '<div class="alignleft actions">';

        // Client filter
        echo '<select name="filter_client">';
        echo '<option value="">' . __('All Clients', 'room35-client-hub') . '</option>';
        echo '<option value="global"' . ($selected_client === 'global' ? ' selected' : '') . '>' . __('Global Files', 'room35-client-hub') . '</option>';
        foreach ($clients as $client) {
            $selected = $selected_client === $client->ID ? ' selected' : '';
            echo '<option value="' . esc_attr($client->ID) . '"' . $selected . '>' . esc_html(R35_Hub_User_Role::get_user_display_name($client->ID)) . '</option>';
        }
        echo '</select>';

        // Source filter
        echo '<select name="filter_source">';
        echo '<option value="">' . __('All Sources', 'room35-client-hub') . '</option>';
        echo '<option value="admin"' . ($selected_source === 'admin' ? ' selected' : '') . '>' . __('Admin Uploads', 'room35-client-hub') . '</option>';
        echo '<option value="client"' . ($selected_source === 'client' ? ' selected' : '') . '>' . __('Client Uploads', 'room35-client-hub') . '</option>';
        echo '</select>';

        submit_button(__('Filter', 'room35-client-hub'), '', 'filter_action', false);

        echo '</div>';
    }

    /**
     * Prepare items for display
     */
    public function prepare_items() {
        global $wpdb;

        $this->process_bulk_action();

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $table = $wpdb->prefix . 'r35_hub_files';

        // Build WHERE clause
        $where = ["status = 'active'"];

        // Filter by client
        if (isset($_GET['filter_client']) && $_GET['filter_client'] !== '') {
            if ($_GET['filter_client'] === 'global') {
                $where[] = "is_global = 1";
            } else {
                $client_id = absint($_GET['filter_client']);
                $where[] = $wpdb->prepare("assigned_to = %d", $client_id);
            }
        }

        // Filter by source
        if (!empty($_GET['filter_source'])) {
            $source = sanitize_text_field($_GET['filter_source']);
            $where[] = $wpdb->prepare("upload_source = %s", $source);
        }

        // Search
        if (!empty($_GET['s'])) {
            $search = '%' . $wpdb->esc_like(sanitize_text_field($_GET['s'])) . '%';
            $where[] = $wpdb->prepare("original_name LIKE %s", $search);
        }

        $where_clause = implode(' AND ', $where);

        // Sorting
        $orderby = isset($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'created_at';
        $order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Validate orderby column
        $allowed_orderby = ['file_name', 'file_size', 'assigned_to', 'created_at', 'original_name'];
        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'created_at';
        }

        // Get total count
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$where_clause}");

        // Get items
        $offset = ($current_page - 1) * $per_page;
        $this->items = $wpdb->get_results(
            "SELECT * FROM {$table}
            WHERE {$where_clause}
            ORDER BY {$orderby} {$order}
            LIMIT {$per_page} OFFSET {$offset}"
        );

        // Set pagination
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);

        // Set columns
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
        ];
    }

    /**
     * Message to display when no items found
     */
    public function no_items() {
        _e('No files found.', 'room35-client-hub');
    }
}
