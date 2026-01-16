<?php
if (!defined('ABSPATH')) exit;

/**
 * Common mail sender function
 */

/* 
function send_custom_mail($to, $subject, $message) {
    $headers = array('Content-Type: text/html; charset=UTF-8');
    return wp_mail($to, $subject, $message, $headers);
}
*/
function send_custom_mail($to, $subject, $message) {
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Human Capital Online <no-reply@humancapitalonline.com>',
        'Reply-To: support@humancapitalonline.com'
    ];
    return wp_mail($to, $subject, $message, $headers);
}










/**
 * Send Registration Welcome Mail
 */
function send_registration_mail($to, $user_name) {
    $subject = "Welcome to Our Website!";

    $login_url = site_url('/login/');

    $message = "
    <html>
    <body style='font-family: Arial, sans-serif; text-align: center;'>
        <h2>Welcome, {$user_name}!</h2>
        <p>Thank you for registering on our website. Your account has been created successfully.</p>
        <p>You can log in anytime using the button below:</p>
        <p><a href='{$login_url}' style='display:inline-block; padding:10px 20px; background:#28a745; color:#fff; text-decoration:none; border-radius:5px;'>Login Now</a></p>
        <p>We’re excited to have you on board 🚀</p>
    </body>
    </html>
    ";

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    wp_mail($to, $subject, $message, $headers);
}









/**
 * Password Reset Mail
 */
function send_password_reset_mail($to, $user_name, $reset_url) {
    $subject = "Password Reset Request";

    $message = "
    <html>
    <body style='font-family: Arial, sans-serif; text-align: center;'>
        <h2>Password Reset Request</h2>
        <p>Hi {$user_name},</p>
        <p>You requested a password reset. Click the button below to reset your password:</p>
        <p><a href='{$reset_url}' style='display:inline-block; padding:10px 20px; background:#0073aa; color:#fff; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
        <p>If you did not request this, please ignore this email.</p>
    </body>
    </html>
    ";

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    wp_mail($to, $subject, $message, $headers);
}
















/**
 * Payment Success Mail
 */
function send_payment_success_mail($to, $user_name, $transaction_id, $expiry_date, $amount) {
    $subject = "Payment Successful - Membership Activated";

    $message = "
    <html>
    <body style='font-family: Arial, sans-serif; text-align: center;'>
        <h2>🎉 Payment Successful!</h2>
        <p>Hi {$user_name},</p>
        <p>We have received your payment successfully.</p>
        
        <table style='margin: 0 auto; border-collapse: collapse;'>
            <tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>Transaction ID</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$transaction_id}</td>
            </tr>
            <tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>Amount Paid</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>₹{$amount}</td>
            </tr>
            <tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>Membership Expiry</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$expiry_date}</td>
            </tr>
        </table>

        <p style='margin-top:20px;'>You can now enjoy full access to premium content 🚀</p>
        <p>Thank you for choosing us!</p>
    </body>
    </html>
    ";

    return send_custom_mail($to, $subject, $message);
}


















/**
 * Invoice Mail
 */
function send_invoice_mail($to, $order_id, $amount) {
    $subject = "Your Invoice - Order #{$order_id}";
    $message = "
        <html>
        <body>
            <h2>Invoice for Order #{$order_id}</h2>
            <p>Total Amount: <strong>₹{$amount}</strong></p>
            <p>Thank you for your purchase!</p>
        </body>
        </html>
    ";
    return send_custom_mail($to, $subject, $message);
}
