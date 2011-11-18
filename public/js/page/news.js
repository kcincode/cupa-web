$(document).ready(function() {
    $('#add-news-container').hide();
   
   
    $('#add-news').click(function(e) {
        e.preventDefault();
        if($('#add-news-container').is(':visible')) {
            $('#add-news-container').fadeOut('fast');
        } else {
            $.ajax({
               type: 'get',
               url: BASE_URL + '/news/add',
               success: function(response) {
                   $('#add-news-container').html(response);
                   $('#add-news-container').fadeIn('fast');
                   $('#team-news').focus();
                   $('#error-string').html('');
                   
                   $('#add-news-submit').click(function(e) {
                       e.preventDefault();
                       
                       if($('#news-title').val() == '') {
                           $('#error-string').html('Please enter a news title.');
                           return;
                       }
                       
                       $.ajax({
                           type: 'post',
                           url: BASE_URL + '/news/add',
                           data: 'title='+$('#news-title').val(),
                           success: function(response) {
                               var obj = eval('(' + response + ')');
                               if(obj.result == 'error') {
                                   $('#error-string').html(obj.message);
                               } else {
                                   window.location = BASE_URL + '/news/' + obj.data + '/edit';
                               }
                           }
                       });
                   });
               }
            });
        }
    });
});