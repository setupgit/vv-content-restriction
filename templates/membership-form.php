<?php if (!defined('ABSPATH')) exit; ?>


    
    <style>
        .justify-between{
            display: flex;
            justify-content: space-between;
        }
        .price-detils-box {
            background-color: #f7f8fa;
            padding: inherit;
            border-radius: inherit;
        }
        .wd-border-top {
            border-top: 1px solid #d8dadd;
        }
        .justify-between.final {
            font-weight: bold;
        }
    </style>
    
    <div class="price-detils-box">
        <h4 class="color-heading text-center">Price Summary:</h4>
        <div class="price-summary wd-text-sm">
            <div class="justify-between">
                <span>Base Price:</span>
                <span class="wd-font-bold">₹ 2,360.00</span>
            </div>
            <div class="justify-between">
                <span>GST (18%):</span>
                <span class="wd-font-bold">₹ 424.80</span>
            </div>
            <div class="wd-border-top">
                <div class="justify-between final">
                    <span>Total Payable:</span>
                    <span>₹ 2,784.80</span>
                </div>
            </div>
        </div>
    </div>

        
    <?php if (is_user_logged_in()): ?>    
        

    <div class="payment-button-div text-center">
        <button id="cr-pay-btn" class="btn btn-color-primary btn-style-default btn-shape-semi-round btn-size-small">Buy Now</button>
        <small>Click the button to pay and activate your membership.</small>
    </div>

    <script>
        document.getElementById('cr-pay-btn').addEventListener('click', function () {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=cr_create_order')
            .then(res => res.json())
            .then(data => {
                if (!data.success) return alert('Error creating order');
        
                var o = data.data;
                var options = {
                    "key": "<?php echo defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : ''; ?>",
                    "amount": o.amount, // in paise
                    "currency": o.currency,
                    "name": "Human Capital",
                    // ðŸ‘‡ This line appears right under the total amount in Razorpay modal
                    "description": "â‚¹" + o.base + " + 18% GST = â‚¹" + o.total,
                    "image": "https://humancapitalonline.com/wp-content/uploads/2025/08/Website-HC-Logo-01.webp",
                    "order_id": o.id,
                    "prefill": { "name": o.name, "email": o.email },
                    "notes": {
                        "Base Price (â‚¹)": o.base,
                        "GST (18%)": o.gst,
                        "Total Payable (â‚¹)": o.total
                    },
                    "theme": { "color": "#f4a426" },
                    "handler": function (response) {
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'cr_razorpay_verify',
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
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
