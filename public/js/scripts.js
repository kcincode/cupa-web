$(document).ready(function() {
    $('#login-link').on('click', function() {
        $('#email').focus();
    });
    
    $('#login-submit').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'post',
            url: BASE_URL + '/login',
            data: 'username='+$('#email').val()+'&password='+$('#password').val()+'&remember='+$('#remember').val(),
            success: function(response) {
                var obj = eval('(' + response + ')');
                if(obj.result == 'Error') {
                    $('#login-error').html('<span class="label label-important">' + obj.msg + '</span>');
                    $('#password').val('');
                } else {
                    window.location.reload();
                }

            }
        });
    });
    
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
    
    $('.login-box').click(function(e) {
        e.stopPropagation();
    });
});

