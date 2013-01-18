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

    // login link handler
    $('#login-link').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'post',
            url: BASE_URL + '/login',
            data: 'username='+$('#username').val()+'&password='+$('#password').val(),
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
});
