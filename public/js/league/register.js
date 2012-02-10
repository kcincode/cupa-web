$(document).ready(function() {
	$('#next-personal').click(function(e) {
		var result = validValues(e);
		if(result !== null) {
			alert(result);
			e.PreventDefault();
		}
	});

	parseClicks();

	$('#add-contact').click(function(e){
		e.preventDefault();
		$('#contacts').append('<div class="row"><div class="name"><input type="text" name="contactNames[]" value=""/></div><div class="phone"><input type="text" name="contactPhones[]" value=""/></div><div class="action"><img class="remove-contact" title="Remove Contact" src="'+BASE_URL+'/images/ico-delete.png"/></div>');
		parseClicks();
	});

	$('#birthday').datepicker({
		changeMonth: true,
		changeYear: true,
		yearRange: '1940:y-4',
		dateFormat: 'yy-mm-dd',
		maxDate: '-4Y'
	});
});

function validValues(e)
{
	var cnt = 0
	var error = null;
	$('div.name input').each(function(){
		if(!validateContactName($(this).val())) {
			e.preventDefault();
			error = 'Please enter a name for each contact.';
		}
		cnt++;
	});

	if(cnt < 2) {
		e.preventDefault();
		error = 'You must enter in at least 2 contacts.';
	}

	$('div.phone input').each(function(){
		if(!validateContactPhone($(this).val())) {
			e.preventDefault();
			error = 'Please enter a phone number for each contact.'; 
		}
	});

	return error;
}

function validateContactName(name)
{
	if(name == '') {
		return false;
	}

	return true;	
}

function validateContactPhone(phone)
{
	var phoneNumberPattern = /^\d{3}-\d{3}-\d{4}$/;
	if(!phoneNumberPattern.test(phone)) {
		return false;
	}

	return true;	
}

function parseClicks()
{
	$('.remove-contact').click(function(e) {
	    $(this).parent().parent().remove();
	});
}