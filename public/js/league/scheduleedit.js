$(document).ready(function() {
    $('#day').datetimepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'hh:mm:ss'
    });

    $('#home_team').chosen();
    $('#away_team').chosen();
});


