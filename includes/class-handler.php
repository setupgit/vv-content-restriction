<?php
if (!defined('ABSPATH')) exit;

class CR_Handler {

    const FREE_LIMIT = 3;

    /**
     * Check if current user can access a premium post
     *
     * @param int $post_id
     * @return bool
     */
    public static function can_access_premium($post_id) {
        //wp_die('CR_HANDLER_NEW_CODE_RUNNING');

        // Guest users → no access
        if (!is_user_logged_in()) {
            return false;
        }

        $user_id = get_current_user_id();

        // Paid member → unlimited access
        if (CR_Membership::is_member($user_id)) {
            return true;
        }

        // Get stored data
        $viewed_posts  = get_user_meta($user_id, 'cr_viewed_posts', true);
        $current_month = date('Y-m', current_time('timestamp'));

        /**
         * Init OR reset on:
         * - first time
         * - corrupted data
         * - month change
         */
        if (
            !is_array($viewed_posts) ||
            !isset($viewed_posts['month'], $viewed_posts['posts']) ||
            !is_array($viewed_posts['posts']) ||
            $viewed_posts['month'] !== $current_month
        ) {
            $viewed_posts = [
                'month' => $current_month,
                'posts' => []
            ];
        }

        // Already viewed this article this month
        if (in_array($post_id, $viewed_posts['posts'], true)) {
            return true;
        }

        // Monthly free limit reached
        if (count($viewed_posts['posts']) >= self::FREE_LIMIT) {
            return false;
        }

        // Allow access + track article
        $viewed_posts['posts'][] = (int) $post_id;
        update_user_meta($user_id, 'cr_viewed_posts', $viewed_posts);

        return true;
    }

    /**
     * Get remaining free views for current month
     *
     * @param int $user_id
     * @return int
     */
    public static function get_remaining_trial_views($user_id) {

        $viewed_posts = get_user_meta($user_id, 'cr_viewed_posts', true);

        if (
            !is_array($viewed_posts) ||
            !isset($viewed_posts['posts']) ||
            !is_array($viewed_posts['posts'])
        ) {
            return self::FREE_LIMIT;
        }

        return max(0, self::FREE_LIMIT - count($viewed_posts['posts']));
    }

    /**
     * Handle signup form
     */
    public static function handle_signup_form() {

        if (
            !isset($_POST['cr_signup_nonce']) ||
            !wp_verify_nonce($_POST['cr_signup_nonce'], 'cr_signup_action')
        ) {
            return;
        }

        $username = sanitize_user($_POST['cr_signup_username']);
        $email    = sanitize_email($_POST['cr_signup_email']);
        $password = $_POST['cr_signup_password'];

        if (empty($username) || empty($email) || empty($password)) {
            wp_die('All fields are required.');
        }

        if (username_exists($username)) {
            wp_die('Username already exists.');
        }

        if (email_exists($email)) {
            wp_die('Email already exists.');
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_die('Error creating user.');
        }

        // Auto login
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        wp_redirect(
            add_query_arg('signup', 'success', wp_get_referer() ?: home_url())
        );
        exit;
    }

    /**
     * Init hooks
     */
    public static function init() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cr_signup_submit'])) {
            self::handle_signup_form();
        }
    }
}

// Init
add_action('init', ['CR_Handler', 'init']);

// Login redirect
add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        return site_url('/my-account');
    }
    return $redirect_to;
}, 10, 3);









// Remove admin bar on front end for non-admins
/*
add_action('after_setup_theme', function () {
    if (!current_user_can('manage_options') && !is_admin()) {
        show_admin_bar(false);
    }
});
*/