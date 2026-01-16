<?php
if (!defined('ABSPATH')) exit;

if (is_user_logged_in()) {
    ?>
    <div class="cr-login-wrapper">
        <h3>You are already logged in</h3>
        <p>You are already logged in to your account.</p>
        <a href="<?php echo site_url('/my-account'); ?>" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Go to My Account</a>
    </div>
    <?php
    return;
}

$redirect_url = isset($args['redirect_to']) ? esc_url($args['redirect_to']) : esc_url(home_url($_SERVER['REQUEST_URI']));
?>

<div class="cr-login-wrapper">
    <h1>Welcome Back</h1>
    <p>You need to be logged in to access this premium content.</p>

    <!--<form name="loginform" id="loginform" action="<?php echo esc_url(wp_login_url()); ?>" method="post">-->
    <form name="loginform" id="cr-login-form" method="post">
        <p>
            <label for="user_login">Email or Username</label>
            <input type="text" name="log" id="user_login" class="input" required />
        </p>

        <p>
            <label for="user_pass">Password</label>
            <div style="position: relative;">
                <input type="password" name="pwd" id="cr_login_password" class="input" required style="padding-right: 40px;">
                <span id="toggleLoginPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">SHOW</span>
            </div>
		</p>

        <p class="forgetmenot">
            <label><input name="rememberme" type="checkbox" value="forever" /> Remember Me</label>
        </p>

        <p>
            <input type="submit" name="wp-submit" id="wp-submit" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small" value="Login" />
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_url); ?>" />
        </p>

        <p>
            <a href="<?php echo site_url('/lost-password/'); ?>">Forgot your password?</a>
            |
            <a href="<?php echo site_url('/registration'); ?>">Register</a>
        </p>

        <div id="cr-login-message"></div> <!-- 👈 yaha error/success dikhega -->
        
    </form>

    
</div>

