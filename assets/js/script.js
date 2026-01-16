// Plugin JS
console.log('Content Restriction plugin loaded.');

//Sign Up form password show and hide
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('cr_signup_password');

if (togglePassword && passwordField) {
    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.textContent = type === 'password' ? 'SHOW' : 'HIDE';
    });
}

//Login form password show and hide
const toggleLoginPassword = document.getElementById('toggleLoginPassword');
const loginPasswordField = document.getElementById('cr_login_password');

if (toggleLoginPassword && loginPasswordField) {
    toggleLoginPassword.addEventListener('click', function () {
        const type = loginPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
        loginPasswordField.setAttribute('type', type);
        this.textContent = type === 'password' ? 'SHOW' : 'HIDE';
    });
}

//Login Form AJAX
jQuery(document).ready(function($) {
    $('#cr-login-form').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: cr_ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cr_user_login',
                username: $('#user_login').val(),
                password: $('#cr_login_password').val(),
                nonce: cr_ajax_object.nonce // ✅ NEW (security)
            },
            success: function(response) {
                if (response.success) {
                    $('#cr-login-message').html('<div style="color:green;">' + response.data.message + '</div>');
                    // ✅ FIX: redirect priority from server
                    var target = (response.data && response.data.redirect) 
                                 ? response.data.redirect 
                                 : cr_ajax_object.redirect_url;
                    window.location.href = target;
                } else {
                    $('#cr-login-message').html('<div style="color:red;">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $('#cr-login-message').html('<div style="color:red;">Something went wrong. Please try again.</div>');
            }
        });
    });

    //Signup form AJAX
    $('#cr-signup-form').on('submit', function(e){
        e.preventDefault();

        var form = $(this);
        var messageBox = $('#cr-signup-message');

        $.ajax({
            url: cr_ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cr_user_register',
                username: form.find('[name="username"]').val(),
                email: form.find('[name="email"]').val(),
                password: form.find('[name="password"]').val(),
                nonce: cr_ajax_object.nonce // ✅ NEW
            },
            success: function(response) {
                if (response.success) {
                    messageBox.html('<p style="color:green;">' + response.data.message + '</p>');
                    setTimeout(function(){
                        var target = (response.data && response.data.redirect_url) 
                                     ? response.data.redirect_url 
                                     : cr_ajax_object.redirect_url;
                        window.location.href = target;
                    }, 2000);
                } else {
                    messageBox.html('<p style="color:red;">' + response.data.message + '</p>');
                }
            }
        });
    });
});
