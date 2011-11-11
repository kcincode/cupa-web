$(document).ready(function() {
    $('#add-minutes-container').hide();
   
   
    $('#add-minutes').click(function(e) {
        e.preventDefault();
        if($('#add-minutes-container').is(':visible')) {
            $('#add-minutes-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/board_meeting_minutes/add',
               success: function(response) {
                   $('#add-minutes-container').html(response);
                   $('#add-minutes-container').fadeIn('fast');
                   $('#location-name').focus();
                   $('#error-string').html('');
                   
                   $('#add-minutes-submit').click(function(e) {
                       e.preventDefault();
                       
                       if($('#location-name').val() == '') {
                           $('#error-string').html('Please enter a location.');
                           return;
                       }
                       
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/board_meeting_minutes/add',
                           data: 'location='+$('#location-name').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'error') {
                                   $('#error').html(obj.msg);
                               } else {
                                   window.location = BASE_URL + '/board_meeting_minutes/' + obj.data;
                               }
                           }
                       });
                   });
               }
            });
        }
    });
});