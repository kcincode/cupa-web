$(document).ready(function () {
    $('#captains').chosen();

    $('#colorSelector').ColorPicker({
        color: '#0000ff',
        onShow: function (colpkr) {
            $(colpkr).fadeIn(500);
            return false;
        },
        onHide: function (colpkr) {
            $(colpkr).fadeOut(500);
            return false;
        },
        onChange: function (hsb, hex, rgb) {
            $('#colorSelector div').css('backgroundColor', '#' + hex);
            $('#color_code').val('#' + hex);
        }
    });
    
    
    $('#colorSelector').ColorPickerSetColor($('#color_code').val());
});