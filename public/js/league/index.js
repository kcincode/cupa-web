$(document).ready(function() {
    $('#add-season-container').hide();
   
   
    $('#add-season').click(function(e) {
        e.preventDefault();
        if($('#add-season-container').is(':visible')) {
            $('#add-season-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/leagues_season/add',
               success: function(response) {
                   $('#add-season-container').html(response);
                   $('#add-season-container').fadeIn('fast');
                   $('#season-name').focus();
                   $('#error-string').html('');
                   
                   $('#add-season-submit').click(function(e) {
                       e.preventDefault();
                       
                       if($('#season-name').val() == '') {
                           $('#error-string').html('Please enter a season name.');
                           return;
                       }
                       
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/leagues_season/add',
                           data: 'name='+$('#season-name').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'error') {
                                   $('#error-string').html(obj.message);
                               } else {
                                   window.location = BASE_URL + '/leagues_season/' + obj.data + '/edit';
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
