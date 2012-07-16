$(document).ready(function() {
	$('#user_id').chosen();

	setTimeout(function() { $('ul.message li.success').fadeOut('fast'); }, 750);
});