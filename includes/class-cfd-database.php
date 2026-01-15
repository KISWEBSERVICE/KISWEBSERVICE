<?php
/**
 * Database operations for the plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

class CFD_Database {

    private $wpdb;
    private $categories_table;
    private $files_table;
    private $assignments_table;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->categories_table = $wpdb->prefix . 'cfd_categories';
        $this->files_table = $wpdb->prefix . 'cfd_files';
        $this->assignments_table = $wpdb->prefix . 'cfd_file_assignments';
    }

    /**
     * Get all categories
     */
    public function get_categories() {
        return $this->wpdb->get_results("SELECT * FROM {$this->categories_table} ORDER BY name ASC");
    }

    /**
     * Create a new category
     */
    public function create_category($name, $description = '') {
        return $this->wpdb->insert(
            $this->categories_table,
            array(
                'name' => sanitize_text_field($name),
                'description' => sanitize_textarea_field($description)
            ),
            array('%s', '%s')
        );
    }

    /**
     * Delete a category
     */
    public function delete_category($category_id) {
        return $this->wpdb->delete(
            $this->categories_table,
            array('id' => $category_id),
            array('%d')
        );
    }

    /**
     * Insert a new file record
     */
    public function insert_file($data) {
        $result = $this->wpdb->insert(
            $this->files_table,
            array(
                'filename' => $data['filename'],
                'original_filename' => $data['original_filename'],
                'file_path' => $data['file_path'],
                'file_type' => $data['file_type'],
                'file_size' => $data['file_size'],
                'category_id' => $data['category_id'],
                'uploaded_by' => $data['uploaded_by'],
                'is_universal' => $data['is_universal']
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d')
        );

        if ($result) {
            return $this->wpdb->insert_id;
        }
        return false;
    }

    /**
     * Get file by ID
     */
    public function get_file($file_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->files_table} WHERE id = %d", $file_id)
        );
    }

    /**
     * Get all files with category information
     */
    public function get_all_files() {
        return $this->wpdb->get_results("
            SELECT f.*, c.name as category_name
            FROM {$this->files_table} f
            LEFT JOIN {$this->categories_table} c ON f.category_id = c.id
            ORDER BY f.created_at DESC
        ");
    }

    /**
     * Delete a file
     */
    public function delete_file($file_id) {
        // First delete all assignments
        $this->wpdb->delete(
            $this->assignments_table,
            array('file_id' => $file_id),
            array('%d')
        );

        // Then delete the file record
        return $this->wpdb->delete(
            $this->files_table,
            array('id' => $file_id),
            array('%d')
        );
    }

    /**
     * Assign file to user
     */
    public function assign_file_to_user($file_id, $user_id) {
        return $this->wpdb->replace(
            $this->assignments_table,
            array(
                'file_id' => $file_id,
                'user_id' => $user_id
            ),
            array('%d', '%d')
        );
    }

    /**
     * Remove file assignment
     */
    public function remove_file_assignment($file_id, $user_id) {
        return $this->wpdb->delete(
            $this->assignments_table,
            array(
                'file_id' => $file_id,
                'user_id' => $user_id
            ),
            array('%d', '%d')
        );
    }

    /**
     * Get assigned users for a file
     */
    public function get_file_assignments($file_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare("SELECT user_id FROM {$this->assignments_table} WHERE file_id = %d", $file_id)
        );
    }

    /**
     * Get files for a specific user
     */
    public function get_user_files($user_id, $category_id = null) {
        $sql = "
            SELECT DISTINCT f.*, c.name as category_name
            FROM {$this->files_table} f
            LEFT JOIN {$this->categories_table} c ON f.category_id = c.id
            LEFT JOIN {$this->assignments_table} a ON f.id = a.file_id
            WHERE (f.is_universal = 1 OR a.user_id = %d)
        ";

        $params = array($user_id);

        if ($category_id) {
            $sql .= " AND f.category_id = %d";
            $params[] = $category_id;
        }

        $sql .= " ORDER BY f.created_at DESC";

        return $this->wpdb->get_results($this->wpdb->prepare($sql, $params));
    }

    /**
     * Get files by category
     */
    public function get_files_by_category($category_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare("
                SELECT f.*, c.name as category_name
                FROM {$this->files_table} f
                LEFT JOIN {$this->categories_table} c ON f.category_id = c.id
                WHERE f.category_id = %d
                ORDER BY f.created_at DESC
            ", $category_id)
        );
    }
}
