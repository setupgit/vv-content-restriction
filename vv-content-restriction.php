<?php
/*
Plugin Name: VV Content Restriction
Description: Restrict content using shortcodes and provide membership system.
Version: 1.0.3
Author: Vishal Verma
*/

if (!defined('ABSPATH')) exit;

define('CR_PLUGIN_PATH', plugin_dir_path(__FILE__));

require_once CR_PLUGIN_PATH . 'includes/install.php';
require_once CR_PLUGIN_PATH . 'includes/class-shortcodes.php';
require_once CR_PLUGIN_PATH . 'includes/class-auth.php';
require_once CR_PLUGIN_PATH . 'includes/class-membership.php';
require_once CR_PLUGIN_PATH . 'includes/class-handler.php';
require_once CR_PLUGIN_PATH . 'includes/mail.php';




//razorpay
require_once CR_PLUGIN_PATH . 'includes/class-razorpay.php';




register_activation_hook(__FILE__, ['CR_Install', 'activate']);
register_uninstall_hook(__FILE__, 'cr_uninstall_plugin');

function cr_uninstall_plugin() {
    global $wpdb;
    $table = $wpdb->prefix . 'content_restriction_users';
    $wpdb->query("DROP TABLE IF EXISTS $table");
}



/*add css and js*/
add_action('wp_enqueue_scripts', 'cr_enqueue_assets');
function cr_enqueue_assets() {
    wp_enqueue_style('cr-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('cr-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);
}












/* update plugin by git */
require_once plugin_dir_path( __FILE__ ) . 'updater/github-updater.php';
new VV_GitHub_Updater(
    __FILE__,
    'setupgit/vv-content-restriction'
);


















// === TEMP: URL-based mail tester (remove after testing) ===
/*
Registration:
https://humancapitalonline.com/?cr_mail_test=registration&to=vermavishaloswal@gmail.com&user=Himanshu&key=123

Password reset:
https://humancapitalonline.com/?cr_mail_test=reset&to=vermavishaloswal@gmail.com&user=Himanshu&url=https%3A%2F%2Fhumancapitalonline.com%2Freset-password%2F%3Ftoken%3Dabc&key=123

Payment success:
https://humancapitalonline.com/?cr_mail_test=payment_success&to=vermavishaloswal@gmail.com&user=Himanshu&tx=TXN123&expiry=2025-12-31&amount=1499&key=123

Invoice:
https://humancapitalonline.com/?cr_mail_test=invoice&to=vermavishaloswal@gmail.com&order=INV-2025-001&amount=1499&key=123




// === TEMP: URL-based mail tester (remove after testing) ===
add_action('init', function () {

    // Turn off if not requested
    if (empty($_GET['cr_mail_test'])) {
        return;
    }

    // Simple guard so random public hits don't spam — change/remove after testing
    $secret_in_url = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
    $secret_needed = '123'; // <-- Temporary test key provided by you

    if ($secret_in_url !== $secret_needed) {
        status_header(403);
        wp_die('Forbidden: invalid key.');
    }

    // Collect common params
    $type   = sanitize_text_field(wp_unslash($_GET['cr_mail_test'])); // registration|reset|payment_success|invoice|help
    $to     = isset($_GET['to']) ? sanitize_email(wp_unslash($_GET['to'])) : '';
    $user   = isset($_GET['user']) ? sanitize_text_field(wp_unslash($_GET['user'])) : 'User';

    // Route by type
    switch ($type) {
        case 'registration':
            if (!$to) wp_die('Missing ?to=');
            $ok = function_exists('send_registration_mail')
                ? send_registration_mail($to, $user)
                : wp_die('send_registration_mail() not found');
            break;

        case 'reset':
            if (!$to) wp_die('Missing ?to=');
            $reset_url = isset($_GET['url']) ? esc_url_raw(wp_unslash($_GET['url'])) : site_url('/reset-password/');
            $ok = function_exists('send_password_reset_mail')
                ? send_password_reset_mail($to, $user, $reset_url)
                : wp_die('send_password_reset_mail() not found');
            break;

        case 'payment_success':
            if (!$to) wp_die('Missing ?to=');
            $tx      = isset($_GET['tx']) ? sanitize_text_field(wp_unslash($_GET['tx'])) : 'TEST-TXN-123';
            $expiry  = isset($_GET['expiry']) ? sanitize_text_field(wp_unslash($_GET['expiry'])) : date('Y-m-d', strtotime('+1 year'));
            $amount  = isset($_GET['amount']) ? sanitize_text_field(wp_unslash($_GET['amount'])) : '999';
            $ok = function_exists('send_payment_success_mail')
                ? send_payment_success_mail($to, $user, $tx, $expiry, $amount)
                : wp_die('send_payment_success_mail() not found');
            break;

        case 'invoice':
            if (!$to) wp_die('Missing ?to=');
            $order   = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : '1001';
            $amount  = isset($_GET['amount']) ? sanitize_text_field(wp_unslash($_GET['amount'])) : '999';
            $ok = function_exists('send_invoice_mail')
                ? send_invoice_mail($to, $order, $amount)
                : wp_die('send_invoice_mail() not found');
            break;

        case 'help':
        default:
            header('Content-Type: text/plain; charset=UTF-8');
            echo "Mail test endpoints (append &key=YOUR_SECRET):\n\n";
            $base = site_url('/');
            echo $base . "?cr_mail_test=registration&to=vermavishaloswal@gmail.com&user=Himanshu&key=123\n";
            echo $base . "?cr_mail_test=reset&to=vermavishaloswal@gmail.com&user=Himanshu&url=" . rawurlencode(site_url('/reset-password/?token=abc')) . "&key=123\n";
            echo $base . "?cr_mail_test=payment_success&to=vermavishaloswal@gmail.com&user=Himanshu&tx=TXN123&expiry=2025-12-31&amount=1499&key=123\n";
            echo $base . "?cr_mail_test=invoice&to=vermavishaloswal@gmail.com&order=INV-2025-001&amount=1499&key=123\n";
            exit;
    }

    // Output result
    if ($ok) {
        wp_die('✅ Test mail sent for type: ' . esc_html($type));
    } else {
        wp_die('❌ wp_mail returned false for type: ' . esc_html($type));
    }
});
*/
