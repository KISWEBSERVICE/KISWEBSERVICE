<?php
/**
 * User Role Management
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

class R35_Hub_User_Role {

    /**
     * Initialize user role hooks
     */
    public static function init() {
        // Auto-assign client role to new registrations
        add_action('user_register', [__CLASS__, 'assign_client_role_on_register']);
    }

    /**
     * Assign client role to new user registrations
     *
     * @param int $user_id New user ID
     */
    public static function assign_client_role_on_register($user_id) {
        $user = new WP_User($user_id);

        // Only assign if user doesn't have an admin role
        if (!$user->has_cap('manage_options')) {
            $user->set_role('r35_client');
        }
    }

    /**
     * Create client role
     */
    public static function create_client_role() {
        remove_role('r35_client');

        add_role(
            'r35_client',
            __('Client', 'room35-client-hub'),
            [
                'read' => true,
            ]
        );
    }

    /**
     * Remove client role
     */
    public static function remove_client_role() {
        remove_role('r35_client');
    }

    /**
     * Check if user is a client
     *
     * @param int|null $user_id User ID (null for current user)
     * @return bool
     */
    public static function is_client($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        return in_array('r35_client', (array) $user->roles);
    }

    /**
     * Get all clients
     *
     * @return array Array of WP_User objects
     */
    public static function get_all_clients() {
        return get_users([
            'role' => 'r35_client',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);
    }

    /**
     * Get all users who can have files assigned to them
     * (Clients and any non-admin users)
     *
     * @return array Array of users
     */
    public static function get_assignable_users() {
        $users = get_users([
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        $assignable = [];
        foreach ($users as $user) {
            // Exclude administrators from being assigned files
            if (!user_can($user->ID, 'manage_options')) {
                $assignable[] = $user;
            }
        }

        return $assignable;
    }

    /**
     * Get user display name
     *
     * @param int $user_id User ID
     * @return string
     */
    public static function get_user_display_name($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return __('Unknown User', 'room35-client-hub');
        }

        if (!empty($user->first_name) || !empty($user->last_name)) {
            return trim($user->first_name . ' ' . $user->last_name);
        }

        return $user->display_name;
    }
}
