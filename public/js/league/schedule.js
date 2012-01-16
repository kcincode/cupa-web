$(document).ready(function() {
    $('#add-game-container').dialog({
        modal: true,
        width: 390,
        height: 400,
        autoOpen: false,
        title: 'Create a Game',
        buttons: {
            "Create": function() {
                if(!isValidGameData()) {
                    $('#error-string').html('Please enter all data');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/league/' + league_id + '/schedule/add',
                    data: 'day=' + $('#day').val() + '&week=' + $('#week').val() + '&field=' + $('#field').val() + '&home_team=' + $('#home_team').val() + '&away_team=' + $('#away_team').val(),
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                        if(obj.result == 'error') {
                            $('#error-string').html(obj.message);
                        } else {
                            window.location = BASE_URL + '/league/' + league_id + '/schedule/' + obj.data + '/edit';
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            },
        }
    });


    $('#add-game').click(function(e) {
        e.preventDefault();
        $('#add-game-container').load(BASE_URL + '/league/' + league_id + '/schedule/add').dialog('open');
    });

    $('.week').hover(function(){
        $(this).addClass('highlight');
    }, function() {
        $(this).removeClass('highlight');
    });

    $('.delete a').click(function(e) {
        if(!confirm('Are you sure you would like to delete this game?')) {
            e.preventDefault();
        }
    });
});

function isValidGameData()
{
    if($('#day').val() == '' || $('#week').val() == '' || $('#field').val() == '') {
        return false;
    }

    if($('#home_team').val() == 0 || $('#away_team').val() == 0) {
        return false;
    }

    return true;
}


