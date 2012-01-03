$(document).ready(function() {
    // hide the login window by default
    $('#login-container').hide();

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
        if($('#login-container').is(':visible')) {
            $('#login-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/login',
               success: function(response) {
                   $('#login-container').html(response);
                   $('#login-container').fadeIn('fast');
                   $('#error-string').html('');
                   $('#username').focus();
                   
                   $('#login-submit').click(function(e) {
                       e.preventDefault();
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/login',
                           data: 'username='+$('#username').val()+'&password='+$('#password').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'Error') {
                                   $('#error').html(obj.msg);
                                   $('#password').val('');
                               } else {
                                   window.location.reload();
                               }
                               
                           }
                       });
                   });
               }
            });
        }
    });
    
    // remove the messages if there are any after 3 sec
    setTimeout(function() { $('ul.message').fadeOut('fast'); }, 4000);
});
