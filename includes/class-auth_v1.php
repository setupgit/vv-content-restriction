<?php
if (!defined('ABSPATH')) exit;

class CR_Auth {

    public function __construct() {
        // AJAX for login
        add_action('wp_ajax_cr_user_login', [$this, 'handle_login']);
        add_action('wp_ajax_nopriv_cr_user_login', [$this, 'handle_login']);
        // AJAX for registration
        add_action('wp_ajax_cr_user_register', [$this, 'handle_registration']);
        add_action('wp_ajax_nopriv_cr_user_register', [$this, 'handle_registration']);
        // Load JS
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        // Block wp-login.php
        add_action('init', [$this, 'block_wp_login']);
        // Lost Password
        add_action('wp_ajax_nopriv_cr_lost_password', [$this, 'handle_lost_password']);
        add_action('wp_ajax_cr_lost_password', [$this, 'handle_lost_password']);
        // Reset Password
        add_action('wp_ajax_nopriv_cr_reset_password', [$this, 'handle_reset_password']);
        add_action('wp_ajax_cr_reset_password', [$this, 'handle_reset_password']);
    }





public function handle_lost_password() {
    $user_login = sanitize_text_field($_POST['user_login']);

    $user = get_user_by('email', $user_login);
    if (!$user) {
        $user = get_user_by('login', $user_login);
    }

    if (!$user) {
        wp_send_json_error(['message' => 'No user found with that email/username.']);
    }

    $key = get_password_reset_key($user);
    if (is_wp_error($key)) {
        wp_send_json_error(['message' => 'Could not generate reset key.']);
    }

    // Custom reset URL (avoid wp-login.php)
    $reset_url = home_url("/reset-password/?key=$key&login=" . rawurlencode($user->user_login));

    // ✅ Centralized mail function
    send_password_reset_mail($user->user_email, $user->display_name, $reset_url);

    wp_send_json_success(['message' => 'Password reset link sent to your email.']);
}
















public function handle_reset_password() {
    $key    = sanitize_text_field($_POST['reset_key']);
    $login  = sanitize_user($_POST['reset_login']);
    $pass1  = $_POST['password_1'];
    $pass2  = $_POST['password_2'];

    if ($pass1 !== $pass2) {
        wp_send_json_error(['message' => 'Passwords do not match.']);
    }

    $user = check_password_reset_key($key, $login);
    if (is_wp_error($user)) {
        wp_send_json_error(['message' => 'Invalid or expired reset key.']);
    }

    reset_password($user, $pass1);

    wp_send_json_success([
        'message' => 'Password reset successful. Please login with your new password.',
        'redirect' => home_url('/login/')
    ]);
}













    public function enqueue_scripts() {
        wp_enqueue_script('cr-auth-js', plugins_url('../assets/js/script.js', __FILE__), ['jquery'], null, true);
        wp_localize_script('cr-auth-js', 'cr_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'redirect_url' => home_url()
        ]);
    }










    public function handle_login() {
        $creds = [];
        $creds['user_login']    = sanitize_user($_POST['username']);
        $creds['user_password'] = $_POST['password'];
        $creds['remember']      = true;

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            // Stay on same page with error message
            wp_send_json_error([
                'message' => __('Invalid username or password.', 'cr')
            ]);
        } else {
            // If subscriber/customer → redirect frontend
            if (in_array('subscriber', $user->roles) || in_array('customer', $user->roles)) {
                wp_send_json_success([
                    'message'  => __('Login successful.', 'cr'),
                    'redirect' => home_url('/login/') // ya custom dashboard ka URL
                ]);
            }

            // Else normal users (editor/admin etc.)
            wp_send_json_success([
                'message'  => __('Login successful.', 'cr'),
                'redirect' => admin_url()
            ]);
        }
    }








    public function block_wp_login() {
        global $pagenow;
        if ($pagenow === 'wp-login.php' && !is_user_logged_in()) {
            wp_safe_redirect(home_url('/login/'));
            exit;
        }
    }








    public function handle_registration() {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        if (username_exists($username) || email_exists($email)) {
            wp_send_json_error(['message' => 'Username or email already exists.']);
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Registration failed.']);
        }
        send_registration_mail($email, $username);

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        wp_send_json_success(array(
            'message' => 'Registration successful! Redirecting...',
            'redirect_url' => home_url('/')
        ));
    }
}

// Initialize auth handler
new CR_Auth();
