<?php
if (!defined('ABSPATH')) exit;

class CR_Handler {

    /**
     * Check if current user can access a premium post
     *
     * @param int $post_id
     * @return bool
     */
    public static function can_access_premium($post_id) {
        if (!is_user_logged_in()) {
            return false;
        }

        $user_id = get_current_user_id();

        // Check membership
        if (CR_Membership::is_member($user_id)) {
            return true;
        }

        // Get viewed posts
        $viewed_posts = get_user_meta($user_id, 'cr_viewed_posts', true);
        if (!is_array($viewed_posts)) {
            $viewed_posts = [];
        }

        // Already viewed this post
        if (in_array($post_id, $viewed_posts)) {
            return true;
        }

        // If already viewed 3 or more different posts
        if (count($viewed_posts) >= 3) {
            return false;
        }

        // Otherwise, allow access and track this post
        $viewed_posts[] = $post_id;
        update_user_meta($user_id, 'cr_viewed_posts', array_unique($viewed_posts));

        return true;
    }

    /**
     * Reset viewed post history for testing or admin use
     */
    public static function reset_user_views($user_id) {
        delete_user_meta($user_id, 'cr_viewed_posts');
    }

    /**
     * Get remaining free views
     *
     * @return int
     */
    public static function get_remaining_trial_views($user_id) {
        $viewed_posts = get_user_meta($user_id, 'cr_viewed_posts', true);
        if (!is_array($viewed_posts)) {
            $viewed_posts = [];
        }
        return max(0, 3 - count($viewed_posts));
    }

    /**
     * Handle signup form
     */
    public static function handle_signup_form() {
        if (!isset($_POST['cr_signup_nonce']) || !wp_verify_nonce($_POST['cr_signup_nonce'], 'cr_signup_action')) {
            return;
        }

        $username = sanitize_user($_POST['cr_signup_username']);
        $email = sanitize_email($_POST['cr_signup_email']);
        $password = $_POST['cr_signup_password'];

        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            wp_die('All fields are required.');
        }

        if (username_exists($username)) {
            wp_die('Username already exists.');
        }

        if (email_exists($email)) {
            wp_die('Email already exists.');
        }

        // Create user
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_die('Error creating user.');
        }

        // Log in the new user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        //wp_redirect(home_url());
        wp_redirect(add_query_arg('signup', 'success', wp_get_referer() ?: home_url()));
        exit;
    }

    /**
     * Hook into init to handle signup
     */
    public static function init() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cr_signup_submit'])) {
            self::handle_signup_form();
        }
    }
}

add_action('init', ['CR_Handler', 'init']);





// Add custom login redirect to my account page
add_filter('login_redirect', function($redirect_to, $request, $user) {
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