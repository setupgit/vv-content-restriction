<?php if (!defined('ABSPATH')) exit; ?>

<?php if (is_user_logged_in()): ?>
    <div class="payment-button-div text-center">
        <button id="cr-pay-btn" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Buy Now</button>
        <!--<button id="rzp-button1">Buy Now</button>-->
        <small>Click the button below to pay and activate your membership.</small>
    </div>

    <script>
    document.getElementById('cr-pay-btn').addEventListener('click', function () {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=cr_create_order')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return alert('Error creating order');

            var options = {
                /* "key": "<?php // echo 'rzp_test_kOT2liOKoiWTlm'; ?>", // ORIGINAL KEY: rzp_live_XViAOdH4Y1tElZ Razorpay key  //TEST KEY: rzp_test_kOT2liOKoiWTlm */
                "key": "<?php echo defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : ''; ?>",
                "amount": data.data.amount,
                "currency": data.data.currency,
                "name": "Library Membership",
                "description": "Premium Access for 1 Year",
                "order_id": data.data.id,
                "handler": function (response) {
                    // Post to server to validate and activate
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            action: 'cr_razorpay_verify',
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature
                        })
                    }).then(r => r.json()).then(res => {
                        if (res.success) {
                            window.location.href = '<?php echo home_url('/thank-you'); ?>';
                        } else {
                            alert('Payment verification failed');
                        }
                    });
                }
            };
            new Razorpay(options).open();
        });
    });
    </script>
<?php else: ?>
    <div class="payment-button-div text-center">
        <a href="/login/" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Login</a>
        <small class="sub-btn-warning">You must be logged in to purchase membership.</small>
    </div>
<?php endif; ?>
