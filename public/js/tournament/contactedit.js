$(document).ready(function(){
    $('#user_id').chosen();

    $('#user_id').change(function(){
        if($(this).val() != 0) {
            $('#name').val('');
        }
    });

    $('#name').keyup(function(){
        $('#user_id').val('0');
        $('.chzn-container').remove();
        $('#user_id').removeClass('chzn-done').chosen();
    });
});