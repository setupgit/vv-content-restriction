<?php
if (!defined('ABSPATH')) exit;

/**
 * Global mail identity (From) via filters — applies to all wp_mail() calls
 * Keeps DMARC/SPF alignment clean when combined with Sender (Return-Path) below.
 */
add_filter('wp_mail_from', function () {
    return 'no-reply@humancapitalonline.com';
});
add_filter('wp_mail_from_name', function () {
    return 'Human Capital Online';
});

/**
 * Set Return-Path / envelope MAIL FROM for SPF/DMARC alignment.
 */
add_action('phpmailer_init', function ($phpmailer) {
    if (empty($phpmailer->Sender)) {
        $phpmailer->Sender = 'no-reply@humancapitalonline.com';
    }
});

/**
 * Build standard headers for HTML email.
 * You can pass extra headers via $extra (e.g., different Reply-To for a specific mail).
 */
function cr_build_headers(array $extra = []) {
    $base = [
        'Content-Type: text/html; charset=UTF-8',
        // Keep Reply-To to a monitored inbox (forwarder ok)
        'Reply-To: support@humancapitalonline.com',
    ];
    return array_merge($base, $extra);
}

/**
 * Common sender wrapper — ALWAYS use this to send plugin emails.
 */
function send_custom_mail($to, $subject, $message, $extra_headers = []) {
    // Ensure strings
    $to       = is_array($to) ? $to : (array) $to;
    $subject  = (string) $subject;
    $message  = (string) $message;

    $headers = cr_build_headers($extra_headers);
    return wp_mail($to, $subject, $message, $headers);
}

/** ----------------------- TEMPLATES ----------------------- */

/**
 * Registration Welcome
 */
function send_registration_mail($to, $user_name) {
    $subject   = 'Welcome to Our Website!';
    $login_url = site_url('/login/');

    $user_name_safe = esc_html($user_name);
    $login_url_safe = esc_url($login_url);

    $message = '
    <html>
    <body style="font-family: Arial, sans-serif; text-align: center; color:#222; line-height:1.5;">
        <h2 style="margin:0 0 12px;">Welcome, ' . $user_name_safe . '!</h2>
        <p>Thank you for registering on our website. Your account has been created successfully.</p>
        <p>You can log in anytime using the button below:</p>
        <p style="margin:18px 0;">
            <a href="' . $login_url_safe . '" style="display:inline-block; padding:10px 20px; background:#28a745; color:#fff; text-decoration:none; border-radius:5px;">Login Now</a>
        </p>
        <p>If the button does not work, copy and paste this link into your browser:<br>
            <span style="font-size:12px; color:#555;">' . $login_url_safe . '</span>
        </p>
        <p style="margin-top:16px;">We\'re excited to have you on board.</p>
    </body>
    </html>';

    return send_custom_mail($to, $subject, $message);
}

/**
 * Password Reset
 */
function send_password_reset_mail($to, $user_name, $reset_url) {
    $subject      = 'Password Reset Request';
    $user_safe    = esc_html($user_name);
    $reset_safe   = esc_url($reset_url);

    $message = '
    <html>
    <body style="font-family: Arial, sans-serif; text-align: center; color:#222; line-height:1.5;">
        <h2 style="margin:0 0 12px;">Password Reset Request</h2>
        <p>Hi ' . $user_safe . ',</p>
        <p>You requested a password reset. Click the button below to reset your password:</p>
        <p style="margin:18px 0;">
            <a href="' . $reset_safe . '" style="display:inline-block; padding:10px 20px; background:#0073aa; color:#fff; text-decoration:none; border-radius:5px;">Reset Password</a>
        </p>
        <p>If the button does not work, copy and paste this link into your browser:<br>
            <span style="font-size:12px; color:#555;">' . $reset_safe . '</span>
        </p>
        <p>If you did not request this, please ignore this email.</p>
    </body>
    </html>';

    return send_custom_mail($to, $subject, $message);
}

/**
 * Payment Success
 */
function send_payment_success_mail($to, $user_name, $transaction_id, $expiry_date, $amount) {
    $subject = 'Payment Successful - Membership Activated';

    $message = '
    <html>
    <body style="font-family: Arial, sans-serif; text-align: center; color:#222; line-height:1.5;">
        <h2 style="margin:0 0 12px;">Payment Successful</h2>
        <p>Hi ' . esc_html($user_name) . ',</p>
        <p>We have received your payment successfully.</p>
        <table style="margin:12px auto; border-collapse:collapse; font-size:14px;">
            <tr>
                <td style="padding:8px 10px; border:1px solid #ddd; text-align:left;">Transaction ID</td>
                <td style="padding:8px 10px; border:1px solid #ddd; text-align:left;">' . esc_html($transaction_id) . '</td>
            </tr>
            <tr>
                <td style="padding:8px 10px; border:1px solid #ddd; text-align:left;">Amount Paid</td>
                <td style="padding:8px 10px; border:1px solid #ddd; text-align:left;">&#8377;' . esc_html($amount) . '</td>
            </tr>
            <tr>
                <td style="padding:8px 10px; border:1px solid #ddd; text-align:left;">Membership Expiry</td>
                <td style="padding:8px 10px; border:1px solid #ddd; text-align:left;">' . esc_html($expiry_date) . '</td>
            </tr>
        </table>
        <p style="margin-top:16px;">You now have full access to premium content.</p>
        <p>Thank you for choosing us!</p>
    </body>
    </html>';

    return send_custom_mail($to, $subject, $message);
}

/**
 * Invoice
 */
function send_invoice_mail($to, $order_id, $amount) {
    $subject = 'Your Invoice - Order #' . preg_replace('/[^0-9A-Za-z\-]/', '', (string) $order_id);

    $message = '
    <html>
    <body style="font-family: Arial, sans-serif; color:#222; line-height:1.5;">
        <h2 style="text-align:center; margin:0 0 12px;">Invoice for Order #' . esc_html($order_id) . '</h2>
        <p style="text-align:center;">Total Amount: <strong>&#8377;' . esc_html($amount) . '</strong></p>
        <p style="text-align:center;">Thank you for your purchase!</p>
    </body>
    </html>';

    return send_custom_mail($to, $subject, $message);
}
