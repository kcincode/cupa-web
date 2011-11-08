$(document).ready(function() {
    $('#add-officers-container').hide();
   
    $('#add-officer').click(function(e) {
        e.preventDefault();
        if($('#add-officers-container').is(':visible')) {
            $('#add-officers-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/officers/add',
               success: function(response) {
                   $('#add-officers-container').html(response);
                   $('#add-officers-container').fadeIn('fast');
                   $('#officers-position').focus();
                   $('#error-string').html('');
                   
                   $('#add-officers-submit').click(function(e) {
                       e.preventDefault();
                       
                       if($('#officers-position').val() == '') {
                           $('#error-string').html('Please enter a position.');
                           return;
                       }
                       
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/officers/add',
                           data: 'position='+$('#officers-position').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'error') {
                                   $('#error').html(obj.msg);
                               } else {
                                   window.location = BASE_URL + '/officers/' + obj.data;
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