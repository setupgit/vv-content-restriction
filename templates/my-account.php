<?php if (!defined('ABSPATH')) exit; ?>

<?php if (is_user_logged_in()): 
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    global $wpdb;
    $table = $wpdb->prefix . 'content_restriction_payments';

    // Get all membership records for this user
    $memberships = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY expiry_date DESC", $user_id)
    );
?>

<div class="my-account">
    <h2>Welcome, <?php echo esc_html($current_user->display_name); ?>!</h2>
    <p><strong>Email:</strong> <?php echo esc_html($current_user->user_email); ?></p>
    <p><strong>Username:</strong> <?php echo esc_html($current_user->user_login); ?></p>
    <!--<p><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">Logout</a></p>-->

    <hr>

    <h3>Your Membership(s)</h3>

    <?php if (!empty($memberships)): ?>
        <figure class="wp-block-table is-style-stripes">
            <table class="cr-membership-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Transaction ID</th>
                        <th>Status</th>
                        <th>Expiry Date</th>
                        <th>Current?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memberships as $membership): 
                        $is_active = strtotime($membership->expiry_date) > time();
                    ?>
                        <tr>
                            <td>Online Library Membership</td>
                            <td><?php echo esc_html($membership->transaction_id); ?></td>
                            <td><?php echo esc_html(ucfirst($membership->status)); ?></td>
                            <td><?php echo date('F j, Y', strtotime($membership->expiry_date)); ?></td>
                            <td><?php echo $is_active ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">Expired</span>'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </figure>
    <?php else: ?>
        <p>You have not purchased any memberships yet.</p>
    <?php endif; ?>
</div>

<?php else: ?>
    <div class="cr-signup-wrapper">
        <h3>You must be logged in to check your account</h3>
        <p>You are not logged in.</p>
        <a href="<?php echo site_url('/login'); ?>" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Login</a>
    </div>
<?php endif; ?>
