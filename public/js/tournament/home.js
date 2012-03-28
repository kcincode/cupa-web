$(document).ready(function() {
    $('#description .link').hide();
    
    $('#description').hover(function(){
         $('#description .link').show();
         $(this).addClass('highlight');
    }, function() {
        $('#description .link').hide();
         $(this).removeClass('highlight');
    });
    
    $('#add-update-container').hide();
    
    $('#add-update').click(function(e){
        e.preventDefault();
        $('#add-update-container').dialog({
            modal: true,
            width: 270,
            height: 160,
            buttons: {
                "Create": function() {
                    if($('#title').val() == '') {
                        $('#error-string').html('Please enter an update title.');
                        return;
                    }

                    $.ajax({
                        type: 'post',
                        url: BASE_URL + '/tournament/' + $('#tournament-name').val() + '/' + $('#tournament-year').val(),
                        data: 'title='+$('#title').val(),
                        success: function(response) {
                            var obj = eval('(' + response + ')');
                            if(obj.result == 'error') {
                                $('#error-string').html(obj.message);
                            } else {
                                window.location = obj.url;
                            }
                        }
                    });
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });
    });
    
});