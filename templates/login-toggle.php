<?php
if (!defined('ABSPATH')) exit;

if (is_user_logged_in()): 
    $current_user = wp_get_current_user();
    $logout_url   = wp_logout_url(home_url());
    $account_url  = site_url('/my-account/'); // agar slug alag hai to update karna
?>
    <div class="wd-button-wrapper text-center">
        <span class="cr-welcome">👋 WELCOME, <?php echo esc_html($current_user->display_name); ?> </span>&nbsp;&nbsp;

        <style>
            .wd-tools-icon:before {
             content: "\f124";
            font-family: "woodmart-font";
            vertical-align: middle;
            color: #fff;
            }   
        </style>

        <a href="<?php echo esc_url($account_url); ?>" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-extra-small">
            <span class="wd-tools-icon"> </span> My Account
        </a>

        <a href="<?php echo esc_url($logout_url); ?>" 
           class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-extra-small">
           Logout
        </a>
    </div>
<?php else: 
    $login_url = site_url('/login/');
?>
    <div class="wd-button-wrapper text-center">
        <a href="<?php echo esc_url($login_url); ?>" 
           class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-extra-small">
           Sign In
        </a>
    </div>
<?php endif; ?>
