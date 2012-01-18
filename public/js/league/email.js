$(document).ready(function(){
    $('#send-button').click(function(e){
        $(this).prop('value', 'Sending Emails');
        $(this).prop('disabled', 'disabled');
        $(this).addClass('disabled');
    });
});