<?php if (!defined('ABSPATH')) exit; ?>

<div class="cr-login-wrapper">
    <h1>Forgot Password ?</h1>
    <p>Enter your email or username to receive a password reset link.</p>

    <form id="cr-lost-password" method="post">
        <p>
            <label for="user_login">Email or Username</label>
            <input type="text" name="user_login" id="user_login" class="input" required />
        </p>

        <p>
            <button type="submit" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">
                Send Reset Link
            </button>
        </p>

        <div class="cr-message"></div> <!-- 👈 Ajax response msg -->
    </form>

    <p>
        <a href="<?php echo site_url('/login/'); ?>">Back to Login</a>
    </p>
</div>

<script>
jQuery(document).ready(function($){
    $('#cr-lost-password').on('submit', function(e){
        e.preventDefault();

        var form = $(this);
        var messageBox = form.find('.cr-message');

        $.ajax({
            url: cr_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'cr_lost_password',
                user_login: form.find('[name="user_login"]').val()
            },
            success: function(response) {
                if (response.success) {
                    messageBox.html('<p style="color:green;">' + response.data.message + '</p>');
                } else {
                    messageBox.html('<p style="color:red;">' + response.data.message + '</p>');
                }
            }
        });
    });
});
</script>
