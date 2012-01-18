$(document).ready(function(){
    $('#email-form').submit(function() {
        $('#send-button').prop('value', 'Sending Emails');
        $('#send-button').prop('disabled', 'disabled');
        $('#send-button').addClass('disabled');
    });
});

