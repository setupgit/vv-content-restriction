<?php
if (!defined('ABSPATH')) exit;

class CR_Membership {

    public function __construct() {
        // Handle form submission (basic POST handler, non-AJAX)
        add_action('init', [$this, 'handle_membership_submission']);
    }

    /**
     * Handle membership form submission
     */
    public function handle_membership_submission() {
        if (isset($_POST['cr_buy_membership']) && is_user_logged_in()) {
            $user_id = get_current_user_id();
            $duration = 365 * DAY_IN_SECONDS; // 1 year
            $expiry_date = date('Y-m-d H:i:s', time() + $duration);

            // Save to user meta
            update_user_meta($user_id, 'cr_membership_expiry', $expiry_date);

            // Insert into custom table
            global $wpdb;
            $table = $wpdb->prefix . 'content_restriction_payments';

            $wpdb->insert($table, [
                'user_id' => $user_id,
                'transaction_id' => uniqid('txn_'),
                'status' => 'success',
                'expiry_date' => $expiry_date
            ]);

            wp_redirect(home_url('/membership-success')); // redirect after purchase
            exit;
        }
    }

    /**
     * Static helper to check if user is active member
     */
    public static function is_member($user_id) {
        $expiry = get_user_meta($user_id, 'cr_membership_expiry', true);
        return $expiry && strtotime($expiry) > time();
    }
}

// Initialize
new CR_Membership();
