$(document).ready(function(){
    $('#add-person-container').hide();

    $('#add-person').click(function(e) {
        e.preventDefault();

        $('#add-person-container').dialog({
            title: 'Add a Contact',
            width: 300,
            height: 390,
            modal: true,
            buttons: {
                "Create": function() {
                    if($('#contact-user').val() == 0) {
                        $('#error-string').html('Please select a user.');
                        return;
                    }

                    $.ajax({
                        type: 'post',
                        url: BASE_URL + '/tournament/' + $('#tournament-name').val() + '/' + $('#tournament-year').val() + '/contact',
                        data: 'user_id='+$('#contact-user').val(),
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
        $('#contact-user').chosen();

    });

    $('.contact .actions img').hover(function(){
        $(this).parent().parent().parent().addClass('highlight');
    }, function() {
        $(this).parent().parent().parent().removeClass('highlight');
    });

    $('.contact .actions a.delete').click(function(e){
        if(!confirm('Are you sure you would like to delete this contact?')) {
            e.preventDefault();
        }
    });

});