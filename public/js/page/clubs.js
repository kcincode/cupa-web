$(document).ready(function() {
    $('#add-team-container').hide();
   
   
    $('#add-club').click(function(e) {
        e.preventDefault();
        if($('#add-team-container').is(':visible')) {
            $('#add-team-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/clubs/add',
               success: function(response) {
                   $('#add-team-container').html(response);
                   $('#add-team-container').fadeIn('fast');
                   $('#team-name').focus();
                   $('#error-string').html('');
                   
                   $('#add-team-submit').click(function(e) {
                       e.preventDefault();
                       
                       if($('#team-name').val() == '') {
                           $('#error-string').html('Please enter a team name.');
                           return;
                       }
                       
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/clubs/add',
                           data: 'name='+$('#team-name').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'error') {
                                   $('#error-string').html(obj.message);
                               } else {
                                   window.location = BASE_URL + '/clubs/' + obj.data;
                               }
                           }
                       });
                   });
               }
            });
        }
    });
    
    $('.links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});