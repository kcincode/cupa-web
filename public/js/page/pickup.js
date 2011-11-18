$(document).ready(function() {
    $('#add-pickup-container').hide();
   
   
    $('#add-pickup').click(function(e) {
        e.preventDefault();
        if($('#add-pickup-container').is(':visible')) {
            $('#add-pickup-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/pickup/add',
               success: function(response) {
                   $('#add-pickup-container').html(response);
                   $('#add-pickup-container').fadeIn('fast');
                   $('#pickup-name').focus();
                   $('#error-string').html('');
                   
                   $('#add-pickup-submit').click(function(e) {
                       e.preventDefault();
                       
                       if($('#pickup-name').val() == '') {
                           $('#error-string').html('Please enter a pickup name.');
                           return;
                       }
                       
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/pickup/add',
                           data: 'pickup='+$('#pickup-name').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'error') {
                                   $('#error-string').html(obj.message);
                               } else {
                                   window.location = BASE_URL + '/pickup/' + obj.data;
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