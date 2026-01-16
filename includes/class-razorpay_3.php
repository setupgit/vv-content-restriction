<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'razorpay/Razorpay.php';

use Razorpay\Api\Api;

class CR_Razorpay {

    //private $api_key = 'rzp_test_kOT2liOKoiWTlm'; // Replace with your test/live Razorpay Key ID ORI: rzp_live_XViAOdH4Y1tElZ // TEST: rzp_test_kOT2liOKoiWTlm
    //private $api_secret = 'pXDY1rN1B2J2elPWmgboZFXp'; // Replace with your Razorpay Secret Key ORI: 2lQmh4R9mTQOE241zf59Hik5 // TEST: pXDY1rN1B2J2elPWmgboZFXp
    private $api_key;
    private $api_secret;


    public function __construct() {
        $this->api_key = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '';
        $this->api_secret = defined('RAZORPAY_SECRET') ? RAZORPAY_SECRET : '';


        add_action('wp_enqueue_scripts', [$this, 'enqueue_razorpay_js']);
        add_action('wp_ajax_cr_create_order', [$this, 'create_order']);
        add_action('wp_ajax_nopriv_cr_create_order', [$this, 'create_order']);
        add_action('wp_ajax_cr_razorpay_verify', [$this, 'verify_payment']);
    }

    public function enqueue_razorpay_js() {
        wp_enqueue_script('razorpay-checkout', 'https://checkout.razorpay.com/v1/checkout.js', [], null, true);
    }

    public function create_order() {
        $api = new Api($this->api_key, $this->api_secret);

        $user = wp_get_current_user();
        $orderData = [
            'receipt'         => uniqid(),
            'amount'          => 236000,
            'currency'        => 'INR',
            'payment_capture' => 1
        ];

        $razorpayOrder = $api->order->create($orderData);

        wp_send_json_success([
            'id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'currency' => $razorpayOrder['currency'],
            'name' => $user->display_name,
            'email' => $user->user_email
        ]);
    }


    // Verify Payment
    public function verify_payment() {
        $payment_id = $_POST['razorpay_payment_id'];
        $order_id = $_POST['razorpay_order_id'];
        $signature = $_POST['razorpay_signature'];

        $generated_signature = hash_hmac('sha256', $order_id . "|" . $payment_id, $this->api_secret);

        if ($generated_signature === $signature && is_user_logged_in()) {
            $user_id = get_current_user_id();
            $expiry_date = date('Y-m-d H:i:s', strtotime('+1 year'));

            // Save user meta
            update_user_meta($user_id, 'cr_membership_expiry', $expiry_date);

            // Save in DB
            global $wpdb;
            $wpdb->insert($wpdb->prefix . 'content_restriction_payments', [
                'user_id' => $user_id,
                'transaction_id' => $payment_id,
                'status' => 'success',
                'expiry_date' => $expiry_date
            ]);

            // ✅ Send payment success mail
            //$user = get_userdata($user_id);
            //send_payment_success_mail($user->user_email, $user->display_name, $payment_id, $expiry_date);

            wp_send_json_success(['message' => 'Payment verified.']);
        } else {
            wp_send_json_error(['message' => 'Invalid signature.']);
        }
    }




}
new CR_Razorpay();
