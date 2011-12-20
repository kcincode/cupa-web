$(document).ready(function() {
    $('#add-league-container').hide();
   
   
    $('#add-league').click(function(e) {
        e.preventDefault();
        if($('#add-league-container').is(':visible')) {
            $('#add-league-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/league/add',
               success: function(response) {
                   $('#add-league-container').html(response);
                   $('#add-league-container').fadeIn('fast');
                   $('#league-year').focus();
                   $('#error-string').html('');
                   
                   $('#add-league-submit').click(function(e) {
                       e.preventDefault();
                       
                       if($('#league-year').val() == '') {
                           $('#error-string').html('Please enter the year.');
                           return;
                       }
                       
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/league/add',
                           data: 'year='+$('#league-year').val()+'&season='+season+'&day='+$('#league-day').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'error') {
                                   $('#error-string').html(obj.message);
                               } else {
                                   alert('Season: ' + obj.data);
                                   window.location = BASE_URL + '/leagues/' + obj.data;
                               }
                           }
                       });
                   });
               }
            });
        }
    });

    $('#leagues').tabs();
    
    $('.edit-links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});