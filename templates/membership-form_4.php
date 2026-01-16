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
    
            const o = data.data;
            const totalRupees = (o.amount / 100).toFixed(2);
    
            // 🧾 Show GST breakdown before opening modal
            const confirmMsg =
                `Base Price: ₹${o.base}\n` +
                `GST (18%): ₹${o.gst}\n` +
                `----------------------\n` +
                `Total Payable: ₹${o.total}\n\nProceed to payment?`;
            if (!confirm(confirmMsg)) return;
    
            const options = {
                key: "<?php echo defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : ''; ?>",
                amount: o.amount,          // in paise
                currency: o.currency,
                name: "Human Capital",
                description: "Online Library Membership (₹" + o.base + " + 18% GST)",
                order_id: o.id,
                prefill: { name: o.name, email: o.email },
                notes: {
                    "Base Price (₹)": o.base,
                    "GST 18% (₹)": o.gst,
                    "Total Payable (₹)": o.total
                },
                theme: { color: "#f4a426" },
                handler: function (response) {
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