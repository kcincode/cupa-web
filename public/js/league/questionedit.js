$(document).ready(function(){
    $('#type').change(function() {
        if($(this).val() == 'multiple') {
            answers('show');
        } else {
            answers('hide');
        }
    });
   
    if($('#type').val() != 'multiple') {
        answers('hide');
    }
});

function answers(type)
{
    if(type == 'show') {
        $('#answers-label').show();
        $('#answers-element').show();
    } else if(type == 'hide') {
        $('#answers-label').hide();
        $('#answers-element').hide();
        $('#answers').val('');
    }
}
