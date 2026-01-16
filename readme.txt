=== VV Content Restriction ===
Contributors: Vishal Verma
Tags: membership, content restriction, shortcode
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Restrict premium content using shortcodes and allow membership access.



vv-content-restriction/
│
├── assets/
│   ├── css/
│   │   └── style.css          # UI styling (login form, signup, locked message, payment div)
│   └── js/
│       └── script.js          # Password toggle + AJAX login handling
│
├── includes/
│   ├── class-auth.php         # Login + Registration (AJAX) + block wp-login.php
│   ├── class-handler.php      # Free views (3 articles) + signup form (POST handler)
│   ├── class-membership.php   # Membership activation (manual form)
│   ├── class-razorpay.php     # Razorpay integration (create order, verify payment)
│   ├── class-shortcodes.php   # Shortcodes ([pre], [signup_form], etc.)
│   ├── install.php            # Plugin activation → create DB tables
│   └── razorpay/              # Razorpay SDK library
│
├── templates/
│   ├── locked-message.php     # UI shown when content locked
│   ├── login-form.php         # Login form (AJAX)
│   ├── membership-form.php    # Membership purchase button + Razorpay checkout
│   ├── signup-form.php        # (Not shared yet – but expected similar to login form)
│   └── my-account.php         # (Not shared yet – user dashboard page)
│
├── vv-content-restriction.php    # (Main plugin bootstrap file – not shared yet)
└── uninstall.php              # (uninstall file)