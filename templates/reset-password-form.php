<?php if (!defined('ABSPATH')) exit; ?>

<?php 
$key   = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';

// Agar key ya login nahi hai to direct exit ya redirect
if (empty($key) || empty($login)) 
{ ?>
    <div class="cr-signup-wrapper">
        <h1>Invalid Access</h1>
        <p>You cannot access the password reset page directly</p>
        <a href="<?php echo site_url('/lost-password/'); ?>" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Go to Forgot Password</a>
    </div>
<?php
return;
}
?>

<div class="cr-login-wrapper">
    <h1>Reset Your Password</h1>
    <p>Please enter your new password below.</p>

    <form id="cr-reset-password" method="post">
        <input type="hidden" name="reset_key" value="<?php echo esc_attr($key); ?>">
        <input type="hidden" name="reset_login" value="<?php echo esc_attr($login); ?>">

        <p>
            <label for="password_1">New Password</label>
            <input type="password" name="password_1" id="password_1" class="input" required />
        </p>

        <p>
            <label for="password_2">Confirm Password</label>
            <input type="password" name="password_2" id="password_2" class="input" required />
        </p>

        <p>
            <button type="submit" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">
                Reset Password
            </button>
        </p>

        <div class="cr-message"></div> <!-- Ajax message -->
    </form>

    <p>
        <a href="<?php echo site_url('/lost-password/'); ?>">Back to Forgot Password</a>
    </p>
</div>

<script>
jQuery(document).ready(function($){
    $('#cr-reset-password').on('submit', function(e){
        e.preventDefault();

        var form = $(this);
        var messageBox = form.find('.cr-message');

        $.ajax({
            url: cr_ajax_object.ajax_url,
            type: 'POST',
            data: form.serialize() + '&action=cr_reset_password',
            success: function(response) {
                if (response.success) {
                    messageBox.html('<p style="color:green;">' + response.data.message + '</p>');
                    if (response.data.redirect) {
                        setTimeout(function(){
                            window.location.href = response.data.redirect;
                        }, 2000);
                    }
                } else {
                    messageBox.html('<p style="color:red;">' + response.data.message + '</p>');
                }
            }
        });
    });
});
</script>
