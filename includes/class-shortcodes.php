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
        include CR_PLUGIN_PATH . 'templates/my-account.php';
        return ob_get_clean();
    }

    /**
     * [pre] Premium Content Shortcode
     */
    public function handle_pre_shortcode($atts, $content = null) {

        // Guest user → locked
        if (!is_user_logged_in()) {
            ob_start();
            include CR_PLUGIN_PATH . 'templates/locked-message.php';
            return ob_get_clean();
        }

        $user_id = get_current_user_id();
        $post_id = get_the_ID();

        // Core access decision (ALL logic here)
        if (CR_Handler::can_access_premium($post_id)) {
            return do_shortcode($content);
        }

        // Free limit reached → paywall
        $remaining = CR_Handler::get_remaining_trial_views($user_id);

        ob_start(); ?>
            <div class="locked-content">
                <p><strong>You have reached your free article limit for this month.</strong></p>
                <p>You can read more premium articles by joining our membership.</p>
                <p>
                    <a href="/subscription/" target="_blank"
                       class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small btn-full-width">
                        Join Now
                    </a>
                </p>
                <p style="font-size:12px;opacity:0.7;">
                    Free articles remaining this month: <?php echo esc_html($remaining); ?>
                </p>
            </div>
        <?php
        return ob_get_clean();
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
}

// Init shortcodes
new CR_Shortcodes();
