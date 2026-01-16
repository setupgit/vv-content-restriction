<?php
if (!defined('ABSPATH')) exit;
?>

<?php if (is_user_logged_in()): ?>
    <?php $cr_nonce = wp_create_nonce('cr_razorpay'); ?>
    <div class="payment-button-div text-center">
        <button id="cr-pay-btn" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">
            Buy Now
        </button>
        <small>Click the button below to pay and activate your membership.</small>
    </div>

    <script>
    (function(){
        const btn     = document.getElementById('cr-pay-btn');
        const ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        const nonce   = '<?php echo esc_js($cr_nonce); ?>';

        if (!btn) return;

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            btn.disabled = true;

            // 1) Create order (POST + nonce)
            fetch(ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'cr_create_order',
                    nonce:  nonce
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) {
                    alert((data && data.data && data.data.message) ? data.data.message : 'Error creating order');
                    btn.disabled = false;
                    return;
                }

                // 2) Launch Razorpay
                var options = {
                    key:       data.data.key_id,          // from server
                    amount:    data.data.amount,
                    currency:  data.data.currency,
                    name:      "Library Membership",
                    description: "Premium Access for 1 Year",
                    order_id:  data.data.id,
                    // Optional prefill from server
                    prefill: {
                        name:  (data.data.prefill && data.data.prefill.name)  ? data.data.prefill.name  : '',
                        email: (data.data.prefill && data.data.prefill.email) ? data.data.prefill.email : ''
                    },
                    handler: function (response) {
                        // 3) Verify on server (POST + nonce)
                        fetch(ajaxUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'cr_razorpay_verify',
                                nonce:  nonce,
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id:   response.razorpay_order_id,
                                razorpay_signature:  response.razorpay_signature
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res && res.success) {
                                window.location.href = '<?php echo esc_url(home_url('/thank-you')); ?>';
                            } else {
                                alert((res && res.data && res.data.message) ? res.data.message : 'Payment verification failed');
                                btn.disabled = false;
                            }
                        })
                        .catch(() => {
                            alert('Server error during verification');
                            btn.disabled = false;
                        });
                    },
                    modal: {
                        ondismiss: function () {
                            // user closed the modal
                            btn.disabled = false;
                        }
                    }
                };

                new Razorpay(options).open();
            })
            .catch(() => {
                alert('Server error: could not create order');
                btn.disabled = false;
            });
        });
    })();
    </script>

<?php else: ?>
    <div class="payment-button-div text-center">
        <a href="<?php echo esc_url( home_url('/login/') ); ?>"
           class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Login</a>
        <small class="sub-btn-warning">You must be logged in to purchase membership.</small>
    </div>
<?php endif; ?>
