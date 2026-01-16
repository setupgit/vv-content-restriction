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

        // Block wp-login.php (harder, but with safe allowances)
        add_action('init', [$this, 'block_wp_login']);

        // Lost Password
        add_action('wp_ajax_nopriv_cr_lost_password', [$this, 'handle_lost_password']);
        add_action('wp_ajax_cr_lost_password', [$this, 'handle_lost_password']);

        // Reset Password
        add_action('wp_ajax_nopriv_cr_reset_password', [$this, 'handle_reset_password']);
        add_action('wp_ajax_cr_reset_password', [$this, 'handle_reset_password']);

        // OPTIONAL: logged-in users ko custom /login/ page se nikaal do
        add_action('template_redirect', [$this, 'redirect_logged_in_from_login_page']);
    }

    // ---------- Helpers ----------

    /** 
     * UPDATED: Capabilities ke basis par admin/editor detect
     */
    private function is_admin_like_user($user) {
        if (!$user || is_wp_error($user)) return false;
        return user_can($user, 'manage_options') || user_can($user, 'edit_posts');
    }

    /** 
     * UPDATED: Frontend users ke liye landing
     * Yahan aap apna custom URL set kar sakte ho.
     */
    private function get_frontend_after_login_url() {
        // e.g. /my-account/ ya /login/ (aapki choice)
        return home_url('/my-account/');
    }

    // ---------- Lost Password ----------

    public function handle_lost_password() {
        // UPDATED: nonce check
        $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : ( $_POST['nonce'] ?? '' );
        if ( ! wp_verify_nonce( $nonce, 'cr-auth' ) ) {
            wp_send_json_error(['message' => 'Security check failed.']);
        }

        $user_login = isset($_POST['user_login']) ? sanitize_text_field($_POST['user_login']) : '';

        if ($user_login === '') {
            wp_send_json_error(['message' => 'Please provide email/username.']);
        }

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
        if (function_exists('send_password_reset_mail')) {
            send_password_reset_mail($user->user_email, $user->display_name, $reset_url);
        }

        wp_send_json_success(['message' => 'Password reset link sent to your email.']);
    }

    // ---------- Reset Password ----------

    public function handle_reset_password() {
        // UPDATED: nonce check
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'cr-auth')) {
            wp_send_json_error(['message' => 'Security check failed.']);
        }

        $key    = isset($_POST['reset_key']) ? sanitize_text_field($_POST['reset_key']) : '';
        $login  = isset($_POST['reset_login']) ? sanitize_user($_POST['reset_login']) : '';
        $pass1  = isset($_POST['password_1']) ? $_POST['password_1'] : '';
        $pass2  = isset($_POST['password_2']) ? $_POST['password_2'] : '';

        if ($pass1 === '' || $pass2 === '') {
            wp_send_json_error(['message' => 'Please enter both password fields.']);
        }

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
            // UPDATED: redirect ekdum clear
            'redirect' => home_url('/login/')
        ]);
    }

    // ---------- Assets ----------

    public function enqueue_scripts() {
        // UPDATED: versioning + dependency + in_footer
        wp_enqueue_script(
            'cr-auth-js',
            plugins_url('../assets/js/script.js', __FILE__),
            ['jquery'],
            defined('WP_DEBUG') && WP_DEBUG ? time() : null,
            true
        );

        // UPDATED: redirect_url kept for fallback only, primary redirect server se aayega
        wp_localize_script('cr-auth-js', 'cr_ajax_object', [
            'ajax_url'      => admin_url('admin-ajax.php'),
            'redirect_url'  => home_url('/'), // fallback only
            'nonce'         => wp_create_nonce('cr-auth'), // ✅ NEW
        ]);
    }

    // ---------- Login ----------

    public function handle_login() {
        // UPDATED: nonce check
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cr-auth')) {
            wp_send_json_error(['message' => __('Security Check Failed.', 'cr')]);
        }

        $creds = [];
        $creds['user_login']    = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
        $creds['user_password'] = isset($_POST['password']) ? $_POST['password'] : '';
        $creds['remember']      = true;

        if ($creds['user_login'] === '' || $creds['user_password'] === '') {
            wp_send_json_error(['message' => __('Please enter username and password.', 'cr')]);
        }

        // NOTE: wp_signon already sets the auth cookie
        $user = wp_signon($creds, is_ssl());

        if (is_wp_error($user)) {
            wp_send_json_error([
                'message' => __('Invalid username or password.', 'cr')
            ]);
        }

        // UPDATED: Capabilities first → admin/editor to wp-admin
        if ($this->is_admin_like_user($user)) {
            wp_send_json_success([
                'message'  => __('Login successful.', 'cr'),
                'redirect' => admin_url() // ✅ admin/editor → Dashboard
            ]);
        }

        // UPDATED: Baaki users → frontend area
        wp_send_json_success([
            'message'  => __('Login successful.', 'cr'),
            'redirect' => $this->get_frontend_after_login_url()
        ]);
    }

    // ---------- WP-Login guard ----------

    public function block_wp_login() {
        global $pagenow;

        if ($pagenow !== 'wp-login.php') {
            return;
        }

        // Allow a few native actions if needed; lekin aap khud custom lost/reset use kar rahe ho
        $allowed_actions = array('logout'); // 'postpass', 'rp', 'resetpass', 'lostpassword' ko block hi rakhte hain

        $action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';

        // If already logged in and accidentally hit wp-login.php → admin
        if (is_user_logged_in() && $action !== 'logout') {
            wp_safe_redirect(admin_url());
            exit;
        }

        // Not logged-in users:
        if (!in_array($action, $allowed_actions, true)) {
            // UPDATED: Force custom login page
            wp_safe_redirect(home_url('/login/'));
            exit;
        }
    }

    // ---------- Registration ----------

    public function handle_registration() {
        // UPDATED: nonce check
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cr-auth')) {
            wp_send_json_error(['message' => 'Security check failed!']);
        }

        $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
        $email    = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if ($username === '' || $email === '' || $password === '') {
            wp_send_json_error(['message' => 'All fields are required.']);
        }

        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Invalid email address.']);
        }

        if (username_exists($username) || email_exists($email)) {
            wp_send_json_error(['message' => 'Username or email already exists.']);
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => 'Registration failed.']);
        }

        // OPTIONAL: Default role mapping agar zaroori ho (WordPress default 'subscriber')
        // $user = new WP_User($user_id);
        // $user->set_role('subscriber');

        if (function_exists('send_registration_mail')) {
            send_registration_mail($email, $username);
        }

        // Auto-login new user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true, is_ssl());

        wp_send_json_success([
            'message'       => 'Registration successful! Redirecting...',
            // UPDATED: New users -> frontend area
            'redirect_url'  => $this->get_frontend_after_login_url()
        ]);
    }

    // ---------- UX: Logged-in users should not see /login/ ----------

    public function redirect_logged_in_from_login_page() {
        if (!is_user_logged_in()) return;

        // Agar aapki login page slug '/login/' hai to:
        if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/login/') !== false) {
            $user = wp_get_current_user();

            if ($this->is_admin_like_user($user)) {
                wp_safe_redirect(admin_url());
                exit;
            } else {
                wp_safe_redirect($this->get_frontend_after_login_url());
                exit;
            }
        }
    }
}

// Initialize auth handler
new CR_Auth();
