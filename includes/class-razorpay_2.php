<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'razorpay/Razorpay.php';

use Razorpay\Api\Api;

class CR_Razorpay {

    /** @var string */
    private $api_key;
    /** @var string */
    private $api_secret;

    /** @var int Amount in paise (₹499.00) */
    private $amount_paise = 49900;
    /** @var string */
    private $currency = 'INR';

    public function __construct() {
        // Prefer constants; (optional) fallback to options if you add settings later
        $this->api_key    = defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '';
        $this->api_secret = defined('RAZORPAY_SECRET') ? RAZORPAY_SECRET : '';

        add_action('wp_enqueue_scripts', [$this, 'enqueue_razorpay_js']);

        // AJAX — create order (requires login; also exposed for nopriv but we’ll block inside)
        add_action('wp_ajax_cr_create_order',        [$this, 'create_order']);
        add_action('wp_ajax_nopriv_cr_create_order', [$this, 'create_order']);

        // AJAX — verify payment (requires login)
        add_action('wp_ajax_cr_razorpay_verify',     [$this, 'verify_payment']);
    }

    public function enqueue_razorpay_js() {
        wp_enqueue_script(
            'razorpay-checkout',
            'https://checkout.razorpay.com/v1/checkout.js',
            [],
            null,
            true
        );
    }

    /**
     * Create Razorpay order and return JSON
     * POST: action=cr_create_order, nonce
     */
    public function create_order() {
        // Avoid any accidental output polluting JSON
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        // Nonce (from membership-form.php)
        if (function_exists('check_ajax_referer')) {
            check_ajax_referer('cr_razorpay', 'nonce');
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in to create an order.']);
        }

        try {
            if (empty($this->api_key) || empty($this->api_secret)) {
                wp_send_json_error(['message' => 'Razorpay keys missing on server.']);
            }

            // (Dev safeguard) prevent live keys on local domains
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $is_local = (bool) preg_match('~(localhost|\.test|\.local)~i', $host);
            if ($is_local && str_starts_with($this->api_key, 'rzp_live_')) {
                wp_send_json_error(['message' => 'Use TEST keys on local environment.']);
            }

            $api = new Api($this->api_key, $this->api_secret);

            $user = wp_get_current_user();

            $orderData = [
                'receipt'         => uniqid('rcpt_'),
                'amount'          => $this->amount_paise, // in paise
                'currency'        => $this->currency,
                'payment_capture' => 1,
                'notes'           => [
                    'product' => 'Library Membership 1Y',
                    'user'    => (string) $user->ID,
                ],
            ];

            $razorpayOrder = $api->order->create($orderData);

            wp_send_json_success([
                'id'       => $razorpayOrder['id'],
                'amount'   => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'key_id'   => $this->api_key,
                // helpful prefill info (optional on front-end)
                'prefill'  => [
                    'name'  => $user->display_name,
                    'email' => $user->user_email,
                ],
            ]);
        } catch (\Throwable $e) {
            // Return clean JSON (no HTML)
            wp_send_json_error([
                'message' => 'Order create failed',
                'error'   => (defined('WP_DEBUG') && WP_DEBUG) ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Verify Razorpay signature and activate membership
     * POST: action=cr_razorpay_verify, nonce, razorpay_payment_id, razorpay_order_id, razorpay_signature
     */
    public function verify_payment() {
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }

        if (function_exists('check_ajax_referer')) {
            check_ajax_referer('cr_razorpay', 'nonce');
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in to verify payment.']);
        }

        // Sanitize inputs
        $payment_id = isset($_POST['razorpay_payment_id']) ? sanitize_text_field($_POST['razorpay_payment_id']) : '';
        $order_id   = isset($_POST['razorpay_order_id'])   ? sanitize_text_field($_POST['razorpay_order_id'])   : '';
        $signature  = isset($_POST['razorpay_signature'])  ? sanitize_text_field($_POST['razorpay_signature'])  : '';

        if (!$payment_id || !$order_id || !$signature) {
            wp_send_json_error(['message' => 'Missing parameters.']);
        }

        try {
            // Compute signature: order_id|payment_id with secret
            $generated_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, $this->api_secret);

            if (!hash_equals($generated_signature, $signature)) {
                wp_send_json_error(['message' => 'Invalid signature.']);
            }

            $user_id     = get_current_user_id();
            $expiry_date = date('Y-m-d H:i:s', strtotime('+1 year'));

            // Save user meta
            update_user_meta($user_id, 'cr_membership_expiry', $expiry_date);

            // Save in DB
            global $wpdb;
            $table = $wpdb->prefix . 'content_restriction_payments';

            // Basic safety: if table missing, still proceed with meta
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table) {
                $wpdb->insert(
                    $table,
                    [
                        'user_id'        => $user_id,
                        'transaction_id' => $payment_id,
                        'status'         => 'success',
                        'expiry_date'    => $expiry_date,
                        'created_at'     => current_time('mysql'),
                    ],
                    ['%d', '%s', '%s', '%s', '%s']
                );
            }

            // TODO: Send success email if you have mail helper
            // $user = get_userdata($user_id);
            // send_payment_success_mail($user->user_email, $user->display_name, $payment_id, $expiry_date);

            wp_send_json_success(['message' => 'Payment verified.']);
        } catch (\Throwable $e) {
            wp_send_json_error([
                'message' => 'Verification failed',
                'error'   => (defined('WP_DEBUG') && WP_DEBUG) ? $e->getMessage() : null,
            ]);
        }
    }
}

new CR_Razorpay();
