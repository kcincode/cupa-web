$(document).ready(function() {
    $('#other-label').hide();
    $('#other-element').hide();

    $('#primary_interest-Other').click(function(e) {
        if($(this).prop('checked')) {
            $('#other-label').show();
            $('#other-element').show();
        } else {
            $('#other-label').hide();
            $('#other-element').hide();
        }
    });
});
