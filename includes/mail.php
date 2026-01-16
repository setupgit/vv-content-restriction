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
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <title>Welcome to Human Capital Online</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial,Helvetica,sans-serif; color:#222;">
          <!-- Preheader (hidden in email body but visible in inbox preview) -->
          <span style="display:none; max-height:0; max-width:0; opacity:0; overflow:hidden;">
            Welcome to Human Capital Online — your account is ready. Log in to access premium articles and resources.
          </span>
            <br>
          <table role="presentation" align="center" cellpadding="0" cellspacing="0" width="100%" style="max-width:680px; margin:28px auto; background:#ffffff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
            <tr>
              <td style="padding:24px 28px; text-align:left; border-bottom:1px solid #eef0f2;">
                <!-- Brand / Header -->
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="flex-shrink:0;">
                    <a href="https://humancapitalonline.com/" style="color:#9aa4ae; text-decoration:none;">
                        <img width="200" height="40" src="https://humancapitalonline.com/wp-content/uploads/2025/09/Website-HC-Logo-02-png.png" class="attachment-full size-full" alt="Human Capital Online" style="max-width:200px;" decoding="async">
                    </a>
                  </div>
                </div>
              </td>
            </tr>
        
            <tr>
              <td style="padding:28px;">
                <!-- Greeting -->
                <h1 style="margin:0 0 12px; font-size:20px; font-weight:600; color:#111;">Welcome, ' . $user_name_safe . '!</h1>
        
                <p style="margin:0 0 18px; font-size:15px; line-height:1.6; color:#333;">
                  Thank you for registering at <strong>Human Capital Online</strong>. Your account has been created successfully. We’re excited to help you discover high-quality articles, expert insights, and curated resources to grow your knowledge and career.
                </p>
        
                <!-- CTA button -->
                <p style="text-align:left; margin:20px 0;">
                  <a href="'. $login_url_safe .'" style="display:inline-block; padding:12px 22px; background:#00485c; color:#fff; text-decoration:none; font-weight:600; border-radius:6px;">
                    Log in to your account
                  </a>
                </p>
        
                <!-- Fallback link -->
                <p style="margin:10px 0 22px; font-size:13px; color:#666;">
                  If the button above does not work, copy and paste this link into your browser:<br>
                  <a href="' . $login_url_safe . '" style="color:#0b57a4; word-break:break-all;">' . $login_url_safe . '</a>
                </p>
        
                <!-- Helpful next steps -->
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate; border-spacing:0;">
                  <tr>
                    <td style="padding:10px 0;">
                      <strong style="display:block; font-size:14px; color:#111; margin-bottom:6px;">Getting started</strong>
                      <ul style="margin:0; padding-left:18px; color:#555; line-height:1.6;">
                        <li>Explore the latest articles and expert columns.</li>
                        <li>Save articles to read later from your account.</li>
                        <li>Reach out to us anytime at <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a>.</li>
                      </ul>
                    </td>
                  </tr>
                </table>
        
                <hr style="border:none; border-top:1px solid #eef0f2; margin:22px 0;">
        
                <p style="font-size:13px; color:#777; margin:0;">
                  If you did not create this account, please ignore this email or contact our support team. For security, never share your password with anyone.
                </p>
        
              </td>
            </tr>
        
            <tr>
              <td style="padding:18px 28px; background:#fafbfc; border-top:1px solid #eef0f2; border-radius:0 0 8px 8px;">
                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="font-size:12px; color:#8a959f; text-align:left; vertical-align:middle;">
                      <div>Have questions? Email <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a> or visit <a href="https://humancapitalonline.com/" style="color:#0b57a4;">humancapitalonline.com</a></div>
                    </td>
                  </tr>
                  <tr>
                    <td style="text-align:left; vertical-align:middle; font-size:12px; color:#8a959f;">
                      <div>&copy; ' . date('Y') . ' Human Capital Online</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <br>
        </body>
        </html>
    ';
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
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <title>Password Reset — Human Capital Online</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial,Helvetica,sans-serif; color:#222;">
          <!-- Preheader (hidden in email body but visible in inbox preview) -->
          <span style="display:none; max-height:0; max-width:0; opacity:0; overflow:hidden;">
            We received a request to reset your Human Capital Online password. Use the secure link to choose a new password.
          </span>
            <br>
          <table role="presentation" align="center" cellpadding="0" cellspacing="0" width="100%" style="max-width:680px; margin:28px auto; background:#ffffff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
            <tr>
              <td style="padding:24px 28px; text-align:left; border-bottom:1px solid #eef0f2;">
                <!-- Brand / Header -->
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="flex-shrink:0;">
                    <a href="https://humancapitalonline.com/" style="color:#9aa4ae; text-decoration:none;">
                        <img width="200" height="40" src="https://humancapitalonline.com/wp-content/uploads/2025/09/Website-HC-Logo-02-png.png" class="attachment-full size-full" alt="Human Capital Online" style="max-width:200px;" decoding="async">
                    </a>
                  </div>
                </div>
              </td>
            </tr>
        
            <tr>
              <td style="padding:28px;">
                <!-- Greeting -->
                <h1 style="margin:0 0 12px; font-size:20px; font-weight:600; color:#111;">Hi, ' .$user_safe . '!</h1>
        
                <p style="margin:0 0 18px; font-size:15px; line-height:1.6; color:#333;">
                    We received a request to reset the password for your Human Capital Online account.
                </p>
        
                <!-- CTA button -->
                <p style="text-align:left; margin:20px 0;">
                  <a href="'. $reset_safe .'" style="display:inline-block; padding:12px 22px; background:#d64545; color:#ffffff; text-decoration:none; font-weight:600; border-radius:6px;">
                    Reset your password
                  </a>
                </p>
        
                <!-- Fallback link -->
                <p style="margin:10px 0 22px; font-size:13px; color:#666;">
                  If the button does not work, copy and paste this link into your browser:<br>
                  <a href="' . $reset_safe . '" style="color:#0b57a4; word-break:break-all;">' . $reset_safe . '</a>
                </p>
        
                <!-- Helpful next steps -->
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate; border-spacing:0;">
                  <tr>
                    <td style="padding:10px 0;">
                      <strong style="display:block; font-size:14px; color:#111; margin-bottom:6px;">Reset it</strong>
                      <ul style="margin:0; padding-left:18px; color:#555; line-height:1.6;">
                        <li>Click the password reset link given in the email.</li>
                        <li>Enter and confirm your new password, then save changes.</li>
                        <li>Reach out to us anytime at <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a>.</li>
                      </ul>
                    </td>
                  </tr>
                </table>
        
                <hr style="border:none; border-top:1px solid #eef0f2; margin:22px 0;">
        
                <p style="font-size:13px; color:#777; margin:0;">
                  If you did not request this password reset, you can safely ignore this email. For security, never share your password or reset link with anyone.
                </p>
        
              </td>
            </tr>
        
            <tr>
              <td style="padding:18px 28px; background:#fafbfc; border-top:1px solid #eef0f2; border-radius:0 0 8px 8px;">
                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="font-size:12px; color:#8a959f; text-align:left; vertical-align:middle;">
                      <div>Have questions? Email <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a> or visit <a href="https://humancapitalonline.com/" style="color:#0b57a4;">humancapitalonline.com</a></div>
                    </td>
                  </tr>
                  <tr>
                    <td style="text-align:left; vertical-align:middle; font-size:12px; color:#8a959f;">
                      <div>&copy; ' . date('Y') . ' Human Capital Online</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <br>
        </body>
        </html>
    ';

    return send_custom_mail($to, $subject, $message);
}






















/**
 * Payment Success
 */
function send_payment_success_mail($to, $user_name, $transaction_id, $expiry_date, $amount) {
    $subject = 'Payment Successful - Membership Activated';

    $message = '
            <html>
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <title>Payment Successful — Human Capital Online</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial,Helvetica,sans-serif; color:#222;">
          <!-- Preheader (hidden in email body but visible in inbox preview) -->
          <span style="display:none; max-height:0; max-width:0; opacity:0; overflow:hidden;">
             Payment received — your membership is now active. Transaction ID: ' . esc_html($transaction_id) . '.
          </span>
            <br>
          <table role="presentation" align="center" cellpadding="0" cellspacing="0" width="100%" style="max-width:680px; margin:28px auto; background:#ffffff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
            <tr>
              <td style="padding:24px 28px; text-align:left; border-bottom:1px solid #eef0f2;">
                <!-- Brand / Header -->
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="flex-shrink:0;">
                    <a href="https://humancapitalonline.com/" style="color:#9aa4ae; text-decoration:none;">
                        <img width="200" height="40" src="https://humancapitalonline.com/wp-content/uploads/2025/09/Website-HC-Logo-02-png.png" class="attachment-full size-full" alt="Human Capital Online" style="max-width:200px;" decoding="async">
                    </a>
                  </div>
                </div>
              </td>
            </tr>
        
            <tr>
              <td style="padding:28px;">
                <!-- Greeting -->
                <h1 style="margin:0 0 12px; font-size:20px; font-weight:600; color:#111;">Payment Successful</h1>
        
                <p style="margin:0 0 18px; font-size:15px; line-height:1.6; color:#333;">
                    Hi, ' . esc_html($user_name) . '<br>
                    Thank you — we have received your payment successfully. Your membership is now active.
                </p>
        
                <!-- Data Table -->
                <table style="margin:12px auto 18px; border-collapse:collapse; font-size:14px; width:100%; max-width:520px;">
                    <tr>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left; background:#fafbfc; width:40%;"><strong>Transaction ID</strong></td>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left;">' . esc_html($transaction_id) . '</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left; background:#fafbfc;"><strong>Amount Paid</strong></td>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left;">  &#8377; ' . esc_html($amount) . '</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left; background:#fafbfc;"><strong>Membership Expiry</strong></td>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left;">' . esc_html($expiry_date) . '</td>
                    </tr>
                </table>
        

        
                <!-- Helpful next steps -->
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate; border-spacing:0;">
                  <tr>
                    <td style="padding:10px 0;">
                      <strong style="display:block; font-size:14px; color:#111; margin-bottom:6px;">Start it</strong>
                      <ul style="margin:0; padding-left:18px; color:#555; line-height:1.6;">
                        <li>You now have full access to premium content on Human Capital Online.</li>
                        <li>Reach out to us anytime at <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a>.</li>
                      </ul>
                    </td>
                  </tr>
                </table>
        
                <hr style="border:none; border-top:1px solid #eef0f2; margin:22px 0;">
        
                <p style="font-size:13px; color:#777; margin:0;">
                  For security, save your transaction details and do not share your payment or login information with anyone.
                </p>
        
              </td>
            </tr>
        
            <tr>
              <td style="padding:18px 28px; background:#fafbfc; border-top:1px solid #eef0f2; border-radius:0 0 8px 8px;">
                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="font-size:12px; color:#8a959f; text-align:left; vertical-align:middle;">
                      <div>Have questions? Email <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a> or visit <a href="https://humancapitalonline.com/" style="color:#0b57a4;">humancapitalonline.com</a></div>
                    </td>
                  </tr>
                  <tr>
                    <td style="text-align:left; vertical-align:middle; font-size:12px; color:#8a959f;">
                      <div>&copy; ' . date('Y') . ' Human Capital Online</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <br>
        </body>
        </html>
    ';

    return send_custom_mail($to, $subject, $message);
}




















/**
 * Invoice
 */
function send_invoice_mail($to, $order_id, $amount) {
    $subject = 'Your Invoice - Order #' . preg_replace('/[^0-9A-Za-z\-]/', '', (string) $order_id);

    $message = '
    <html>
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width,initial-scale=1">
          <title>Payment Successful — Human Capital Online</title>
        </head>
        <body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial,Helvetica,sans-serif; color:#222;">
          <!-- Preheader (hidden in email body but visible in inbox preview) -->
          <span style="display:none; max-height:0; max-width:0; opacity:0; overflow:hidden;">
             Payment received — your membership is now active. Transaction ID: ' . esc_html($transaction_id) . '.
          </span>
            <br>
          <table role="presentation" align="center" cellpadding="0" cellspacing="0" width="100%" style="max-width:680px; margin:28px auto; background:#ffffff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
            <tr>
              <td style="padding:24px 28px; text-align:left; border-bottom:1px solid #eef0f2;">
                <!-- Brand / Header -->
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="flex-shrink:0;">
                    <a href="https://humancapitalonline.com/" style="color:#9aa4ae; text-decoration:none;">
                        <img width="200" height="40" src="https://humancapitalonline.com/wp-content/uploads/2025/09/Website-HC-Logo-02-png.png" class="attachment-full size-full" alt="Human Capital Online" style="max-width:200px;" decoding="async">
                    </a>
                  </div>
                </div>
              </td>
            </tr>
        
            <tr>
              <td style="padding:28px;">
                <!-- Greeting -->
                <h1 style="margin:0 0 12px; font-size:20px; font-weight:600; color:#111;">Payment Successful</h1>
        
                <p style="margin:0 0 18px; font-size:15px; line-height:1.6; color:#333;">
                    Hi, ' . esc_html($user_name) . '<br>
                    Thank you — we have received your payment successfully. Your membership is now active.
                </p>
        
                <!-- Data Table -->
                <table style="margin:12px auto 18px; border-collapse:collapse; font-size:14px; width:100%; max-width:520px;">
                    <tr>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left; background:#fafbfc; width:40%;"><strong>Transaction ID</strong></td>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left;">' . esc_html($transaction_id) . '</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left; background:#fafbfc;"><strong>Amount Paid</strong></td>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left;">  &#8377; ' . esc_html($amount) . '</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left; background:#fafbfc;"><strong>Membership Expiry</strong></td>
                        <td style="padding:10px 12px; border:1px solid #eee; text-align:left;">' . esc_html($expiry_date) . '</td>
                    </tr>
                </table>
        

        
                <!-- Helpful next steps -->
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:separate; border-spacing:0;">
                  <tr>
                    <td style="padding:10px 0;">
                      <strong style="display:block; font-size:14px; color:#111; margin-bottom:6px;">Start it</strong>
                      <ul style="margin:0; padding-left:18px; color:#555; line-height:1.6;">
                        <li>You now have full access to premium content on Human Capital Online.</li>
                        <li>Reach out to us anytime at <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a>.</li>
                      </ul>
                    </td>
                  </tr>
                </table>
        
                <hr style="border:none; border-top:1px solid #eef0f2; margin:22px 0;">
        
                <p style="font-size:13px; color:#777; margin:0;">
                  For security, save your transaction details and do not share your payment or login information with anyone.
                </p>
        
              </td>
            </tr>
        
            <tr>
              <td style="padding:18px 28px; background:#fafbfc; border-top:1px solid #eef0f2; border-radius:0 0 8px 8px;">
                <!-- Footer -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="font-size:12px; color:#8a959f; text-align:left; vertical-align:middle;">
                      <div>Have questions? Email <a href="mailto:support@humancapitalonline.com" style="color:#0b57a4;">support@humancapitalonline.com</a> or visit <a href="https://humancapitalonline.com/" style="color:#0b57a4;">humancapitalonline.com</a></div>
                    </td>
                  </tr>
                  <tr>
                    <td style="text-align:left; vertical-align:middle; font-size:12px; color:#8a959f;">
                      <div>&copy; ' . date('Y') . ' Human Capital Online</div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <br>
        </body>
        </html>
    ';

    return send_custom_mail($to, $subject, $message);
}
