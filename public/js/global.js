$(document).ready(function() {
    // logout link handler
    $('#logout-link').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'get',
            url: BASE_URL + '/logout',
            success: function(response) {
               window.location.reload();
            }
        });
    });

    $('#login-dropdown-link').click(function(e){
        setTimeout('$("#username").focus()', 500);
    });

    var url = (document.URL.indexOf('cincyultimate') != -1) ? 'https://secure85.inmotionhosting.com/~cincyu6/secure_login.php' : BASE_URL + '/secure_login.php';

    // login link handler
    $('#login-link').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'post',
            url: url,
            dataTye: 'json',
            crossDomain: true,
            xhrFields: {
                withCredentials: true
            },
            data: 'username='+$('#username').val()+'&password='+encodeURIComponent($('#password').val())+'&session='+$('#session').val(),
            success: function(response) {
                var obj = eval('(' + response + ')');
                if(obj.result == 'Error') {
                    $('#login-error').hide();
                    $('#login-error').html('<div class="alert alert-error">' + obj.msg + '</div>');
                    $('#password').val('');
                    $('#login-error').fadeIn();
                } else {
                    window.location.reload();
                }
            }
        });
    });

    $('#password').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            $('#login-link').click();
        }
    });
});
