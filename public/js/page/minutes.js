$(document).ready(function() {
    $('#add-minutes-container').dialog({
        modal: true,
        width: 350,
        height: 180,
        autoOpen: false,
        title: 'Create a Minutes Location',
        buttons: {
            "Create": function() {
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
                            $('#error-string').html(obj.message);
                        } else {
                            window.location = BASE_URL + '/board_meeting_minutes/' + obj.data;
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $('#add-minutes').click(function(e) {
        e.preventDefault();
        $('#add-minutes-container').load(BASE_URL + '/board_meeting_minutes/add').dialog('open');
        $('#location-name').focus();
    });
});
