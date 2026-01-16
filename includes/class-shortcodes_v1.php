<?php
if (!defined('ABSPATH')) exit;

class CR_Shortcodes {

    public function __construct() {
        add_shortcode('pre', [$this, 'handle_pre_shortcode']);
        add_shortcode('content_login_form', [$this, 'handle_login_form_shortcode']);
        add_shortcode('membership_form', [$this, 'handle_membership_form_shortcode']);
        add_shortcode('signup_form', [$this, 'handle_signup_form_shortcode']);
        add_shortcode('cr_my_account', [$this, 'render_my_account']);
        add_shortcode('cr_login_toggle', [$this, 'handle_login_toggle_shortcode']);
        add_shortcode('cr_lost_password', [$this, 'handle_lost_password_shortcode']);
        add_shortcode('cr_reset_password', [$this, 'handle_reset_password_shortcode']);
    }


    // [cr_lost_password]
    public function handle_lost_password_shortcode() {
        ob_start();
        include CR_PLUGIN_PATH . 'templates/lost-password-form.php';
        return ob_get_clean();
    }

    // [cr_reset_password]
    public function handle_reset_password_shortcode() {
        ob_start();
        include CR_PLUGIN_PATH . 'templates/reset-password-form.php';
        return ob_get_clean();
    }


    // [cr_login_toggle]
    public function handle_login_toggle_shortcode($atts, $content = null) {
        ob_start();
        include CR_PLUGIN_PATH . 'templates/login-toggle.php';
        return ob_get_clean();
    }


    // [cr_my_account]
    public function render_my_account() {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/my-account.php';
        return ob_get_clean();
    }


    // [pre]...[/pre] → Premium Content Lock
    public function handle_pre_shortcode($atts, $content = null) {
        if (!is_user_logged_in()) {
            ob_start();
            include CR_PLUGIN_PATH . 'templates/locked-message.php';
            return ob_get_clean();
        }

        $user_id = get_current_user_id();
        if ($this->user_has_membership($user_id)) {
            return do_shortcode($content);
        }

        $viewed_posts = $this->get_user_viewed_posts($user_id);
        $post_id = get_the_ID();

        if (!in_array($post_id, $viewed_posts)) {
            if (count($viewed_posts) >= 3) {
                return '<div class="locked-content">
                            <p>You have read your 3 free premium posts.</p>
                            <p><strong> Join our online-library membership now</strong></p>
                            <p><a href="/subscription/" title="" target="_blank" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small btn-full-width">Join Now</a></p>
                        </div>';
            }
            $viewed_posts[] = $post_id;
            update_user_meta($user_id, 'cr_viewed_posts', $viewed_posts);
        }

        return do_shortcode($content);
    }

    // [content_login_form]
    public function handle_login_form_shortcode($atts, $content = null) {
        ob_start();
        include CR_PLUGIN_PATH . 'templates/login-form.php';
        return ob_get_clean();
    }

    // [membership_form]
    public function handle_membership_form_shortcode($atts, $content = null) {
        ob_start();
        include CR_PLUGIN_PATH . 'templates/membership-form.php';
        return ob_get_clean();
    }

    // [signup_form]
    public function handle_signup_form_shortcode($atts, $content = null) {
        ob_start();
        include CR_PLUGIN_PATH . 'templates/signup-form.php';
        return ob_get_clean();
    }

    private function get_user_viewed_posts($user_id) {
        $viewed = get_user_meta($user_id, 'cr_viewed_posts', true);
        return is_array($viewed) ? $viewed : [];
    }

    private function user_has_membership($user_id) {
        $expiry = get_user_meta($user_id, 'cr_membership_expiry', true);
        if (!$expiry) return false;

        return strtotime($expiry) > time();
    }
}

// Initialize shortcodes
new CR_Shortcodes();
