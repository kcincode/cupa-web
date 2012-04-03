$(document).ready(function() {
    $("#tabs").tabs();

    $('.team .remove img').hover(function(){
        $(this).parent().parent().parent().addClass('highlight');
    }, function(){
        $(this).parent().parent().parent().removeClass('highlight');
    });

    $('.team .remove a.remove').click(function(e) {
        if(!confirm('Are you sure you would like to remove this team?')) {
            e.preventDefault();
        }
    });

    $('#add-team-container').hide();
    $('#add-team').click(function(e) {
        $('#add-team-container').dialog({
            title: 'Add a Team',
            width: 300,
            height: 215,
            modal: true,
            buttons: {
            "Create": function() {
                if($('#team-name').val() == '') {
                    $('#error-string').html('Please enter a team name.');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/tournament/' + $('#tournament-name').val() + '/' + $('#tournament-year').val() + '/teams',
                    data: 'team='+$('#team-name').val() + '&division=' + $('#team-division').val(),
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

        })
    });

});
