$(document).ready(function() {
    $('#add-club-container').dialog({
        modal: true,
        width: 365,
        height: 175,
        autoOpen: false,
        title: 'Create a Club Team',
        buttons: {
            "Create": function() {
                if($('#team-name').val() == '') {
                    $('#error-string').html('Please enter a club team name.');
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
            },
            "Cancel": function() {
                $(this).dialog("close");
            },
        }
    });


    $('#add-club').click(function(e) {
        e.preventDefault();
        $('#add-club-container').load(BASE_URL + '/clubs/add').dialog('open');
        $('#team-name').focus();
    });

    $('.links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});
