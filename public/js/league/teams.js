$(document).ready(function () {
    $('.player-container').hide();

    $('.overlay').hide();

    $('.info').hover(function() {
        $(this).addClass('selected');
    }, function() {
        $(this).removeClass('selected');    
    });

    $('#add-team-container').dialog({
        modal: true,
        width: 380,
        height: 185,
        autoOpen: false,
        title: 'Create a Team',
        buttons: {
            "Create": function() {
                if($('#team-name').val() == '') {
                    $('#error-string').html('Please enter the team name.');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: BASE_URL + '/league/team_add',
                    data: 'name='+$('#team-name').val()+'&league='+league,
                    success: function(response) {
                        var obj = eval('(' + response + ')');
                        if(obj.result == 'error') {
                            $('#error-string').html(obj.message);
                        } else {
                            window.location = BASE_URL + '/league/team_edit/' + obj.data;
                        }
                    }
                });
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });


    $('#add-team').click(function(e) {
        e.preventDefault();
        $('#add-team-container').load(BASE_URL + '/league/team_add').dialog('open');
    });

    $('.logo').hover(function (){
        $(this).children('.overlay').show(); 
    }, function() {
        $(this).children('.overlay').hide(); 
    });

    $('.overlay').click(function(e){
        window.location = $(this).data('link');
    });

});
  
function loadPlayers(teamId) {
    if($('.team-' + teamId).is(':visible')) {
        $('.team-' + teamId).toggle();
        $('.team-' + teamId).html('');
    } else {
        $.ajax({
           url: BASE_URL + '/league/load_players/' + teamId,
           success: function(response) {
               $('.team-' + teamId).html(response);  
               $('.team-' + teamId).toggle();
           }
        });
    }
}
