$(document).ready(function() {
    $('#login-container').hide();

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
                   
                   $('#login-submit').click(function(e) {
                       e.preventDefault();
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/login',
                           data: 'username='+$('#username').val()+'&password='+$('#password').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'Error') {
                                   alert(obj.msg);
                               } else {
                                   alert('Login Successful.');
                                   //window.location.reload();
                               }
                               
                           }
                       });
                   });
               }
            });
        }
    });
});
