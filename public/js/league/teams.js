$(document).ready(function () {
    $('.player-container').hide();

    $('.info').hover(function() {
        $(this).addClass('selected');
    }, function() {
        $(this).removeClass('selected');    
    });

    $('#add-team-container').hide();

    $('#add-team').click(function(e) {
        e.preventDefault();
        if($('#add-team-container').is(':visible')) {
            $('#add-team-container').fadeOut('fast');
        } else {
            $.ajax({
                type: 'get',
                url: BASE_URL + '/league/team_add',
                success: function(response) {
                    $('#add-team-container').html(response);
                    $('#add-team-container').fadeIn('fast');
                    $('#team-name').focus();
                    $('#error-string').html('');

                    $('#add-team-submit').click(function(e) {
                        e.preventDefault();

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
                    });
                }
            });
        }
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
