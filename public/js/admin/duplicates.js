$(document).ready(function() {
    $('#merge-link img').click(function(e) {
        if(!confirm('Are you sure you would like to merge all user data to this account?')) {
            e.preventDefault();
        }
    });
});