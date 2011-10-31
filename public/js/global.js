$(document).ready(function() {
    $('#login-container').hide();

    $('#logout-link').click(function(e) {
        alert('Logging Out');
        e.preventDefault();
        $.ajax({
            type: 'get',
            url: BASE_URL + '/logout',
            success: function(response) {
               window.location.reload();
            }
        });
    });

    $('#login-link').click(function(e) {
        e.preventDefault();
        if($('#login-container').is(':visible')) {
            $('#login-container').slideUp('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/login',
               success: function(response) {
                   $('#login-container').html(response);
                   $('#login-container').slideDown('fast');
                   $('#error-string').html('');
                   
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
    
});
