<?php
/**
 * Login Required Template
 *
 * @package Room35_Client_Hub
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle login form submission
$login_error = '';
if (isset($_POST['r35_login_submit'])) {
    $creds = [
        'user_login' => sanitize_user($_POST['log']),
        'user_password' => $_POST['pwd'],
        'remember' => isset($_POST['rememberme']),
    ];

    $user = wp_signon($creds, is_ssl());

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        // Redirect to same page after successful login
        wp_redirect(get_permalink());
        exit;
    }
}

// Get registration URL
$registration_enabled = get_option('users_can_register');
$register_url = wp_registration_url();
$lost_password_url = wp_lostpassword_url();
?>
<div class="r35-client-hub r35-login-page">
    <div class="r35-login-container">
        <div class="r35-login-header">
            <h1><?php _e('Client Portal', 'room35-client-hub'); ?></h1>
            <p><?php _e('Please log in to access your files and documents.', 'room35-client-hub'); ?></p>
        </div>

        <?php if ($login_error) : ?>
            <div class="r35-alert r35-alert-error">
                <?php echo wp_kses_post($login_error); ?>
            </div>
        <?php endif; ?>

        <form class="r35-login-form" method="post">
            <div class="r35-form-group">
                <label for="r35-login-user"><?php _e('Username or Email', 'room35-client-hub'); ?></label>
                <input type="text" name="log" id="r35-login-user" required
                    value="<?php echo isset($_POST['log']) ? esc_attr($_POST['log']) : ''; ?>"
                    placeholder="<?php esc_attr_e('Enter your username or email', 'room35-client-hub'); ?>">
            </div>

            <div class="r35-form-group">
                <label for="r35-login-pass"><?php _e('Password', 'room35-client-hub'); ?></label>
                <input type="password" name="pwd" id="r35-login-pass" required
                    placeholder="<?php esc_attr_e('Enter your password', 'room35-client-hub'); ?>">
            </div>

            <div class="r35-form-group r35-form-row">
                <label class="r35-checkbox-label">
                    <input type="checkbox" name="rememberme" value="1">
                    <span><?php _e('Remember me', 'room35-client-hub'); ?></span>
                </label>
                <a href="<?php echo esc_url($lost_password_url); ?>" class="r35-forgot-link">
                    <?php _e('Forgot password?', 'room35-client-hub'); ?>
                </a>
            </div>

            <button type="submit" name="r35_login_submit" class="r35-btn r35-btn-primary r35-btn-block">
                <?php _e('Log In', 'room35-client-hub'); ?>
            </button>
        </form>

        <?php if ($registration_enabled) : ?>
            <div class="r35-login-footer">
                <p>
                    <?php _e("Don't have an account?", 'room35-client-hub'); ?>
                    <a href="<?php echo esc_url($register_url); ?>"><?php _e('Sign up', 'room35-client-hub'); ?></a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
