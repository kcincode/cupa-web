$(document).ready(function() {
    $('#user_team_select').parent().hide();
    $('#user_team_new').parent().hide();

    if($('#team_select-0').prop('checked')) {
        $('#user_team_new').parent().show();
    }

    if($('#team_select-1').prop('checked')) {
        $('#user_team_select').parent().show();
    }

    if(!$('#team_select-0').length) {
        $('#user_team_select').parent().show();
    }

    $('#team_select-0').on('click', function() {
        $('#user_team_select').parent().hide();
        $('#user_team_new').parent().show();
    });
    $('#team_select-1').on('click', function() {
        $('#user_team_select').parent().show();
        $('#user_team_new').parent().hide();
    });
});
