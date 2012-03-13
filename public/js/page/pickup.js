$(document).ready(function() {
    $('#add-pickup-container').dialog({
        modal: true,
        width: 350,
        height: 180,
        autoOpen: false,
        title: 'Create a Pickup Location',
        buttons: {
            "Create": function() {
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
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $('#add-pickup').click(function(e) {
        e.preventDefault();
        $('#add-pickup-container').load(BASE_URL + '/pickup/add').dialog('open');
        $('#pickup-name').focus();
    });

    $('#add-tournament-container').dialog({
        modal: true,
        width: 350,
        height: 200,
        autoOpen: false,
        title: 'Create a Tournament',
        buttons: {
            "Create": function() {
                if($('#tournament-name').val() == '') {
                    $('#error-string').html('Please enter a tournament name.');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/tournament/add',
                    data: 'tournament='+$('#tournament-name').val() + '&year=' + $('#tournament-year').val(),
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                        if(obj.result == 'error') {
                            $('#error-string').html(obj.message);
                        } else {
                            window.location = BASE_URL + obj.data;
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $('#add-tournament').click(function(e) {
        e.preventDefault();
        $('#add-tournament-container').load(BASE_URL + '/tournament/add').dialog('open');
        $('#tournament-name').focus();
    });

    $('.links').hover(function() {
        $(this).parent().addClass('highlight')
    }, function() {        
        $(this).parent().removeClass('highlight')
    });
});
