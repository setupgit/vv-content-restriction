<?php if (!defined('ABSPATH')) exit; ?>

<?php if (is_user_logged_in()): ?>
    <div class="cr-signup-wrapper">
        <h3>You are already logged in</h3>
        <p>You are already logged in to your account.</p>
        <a href="<?php echo site_url('/my-account'); ?>" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Go to My Account</a>
    </div>
    <?php
    return;?>

<?php else: ?>
    <!--
    <div class="cr-signup-wrapper">
        <h2>Sign Up</h2>
        <form method="post" class="cr-signup-form">
            <?php if (!empty($_GET['signup']) && $_GET['signup'] === 'success'): ?>
                <p class="success-msg">Signup successful. You can now <a href="/login">login</a>.</p>
            <?php elseif (!empty($_GET['signup']) && $_GET['signup'] === 'error'): ?>
                <p class="error-msg"><?php echo esc_html($_GET['message'] ?? 'Signup failed. Please try again.'); ?></p>
            <?php endif; ?>

            <p>
                <label for="cr_signup_username">Username*</label>
                <input type="text" name="cr_signup_username" id="cr_signup_username" required>
            </p>

            <p>
                <label for="cr_signup_email">Email*</label>
                <input type="email" name="cr_signup_email" id="cr_signup_email" required>
            </p>

            <p>
                <label for="cr_signup_password">Password*</label>
                <div style="position: relative;">
                    <input type="password" name="cr_signup_password" id="cr_signup_password" required style="padding-right: 40px;">
                    <span id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">SHOW</span>
                </div>
            </p>



            <?php wp_nonce_field('cr_signup_action', 'cr_signup_nonce'); ?>

            <p>
                <input type="submit" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-extra-default" name="cr_signup_submit" value="Sign Up">
            </p>

            <div id="cr-signup-message"></div>

            <p>
                <a href="<?php echo site_url('/login'); ?>"> Back to login</a>
            </p>
        </form>
    </div>
    -->

    <div class="cr-signup-wrapper">
        <h1>Sign Up</h1>
        <form id="cr-signup-form" method="post">
            <p>
                <label for="cr_signup_username">Username*</label>
                <input type="text" name="username" id="cr_signup_username" required>
            </p>

            <p>
                <label for="cr_signup_email">Email*</label>
                <input type="email" name="email" id="cr_signup_email" required>
            </p>

            <p>
                <label for="cr_signup_password">Password*</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="cr_signup_password" required style="padding-right: 40px;">
                    <span id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">SHOW</span>
                </div>
            </p>

            <p>
                <input type="submit" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small" value="Sign Up">
            </p>

            <div id="cr-signup-message"></div> 

            <p><a href="<?php echo site_url('/login'); ?>">Back to login</a></p>
        </form>
    </div>


<?php endif; ?>
