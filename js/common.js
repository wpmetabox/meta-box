var rwmb = {
	isValid : true,
	validation_message : "Validation Failed. One or more fields below are required.", // TODO: add localization support
};

(function($){

	/*
	*  Exists
	*  @description: returns true / false		
	*/
	$.fn.exists = function()
	{
		return $(this).length>0;
	};

	/*
	*  Document Ready
	*  @description: adds required field styling elements
	*/
	$(document).ready(function(){
	
		$('.rwmb-field.required').each(function(){
			$(this).find('.rwmb-label label').css('font-weight','bold');
			$(this).find('.rwmb-label').append('<span class="required">*</span>')
		});
	
	});

	/*
	*  Submit form
	*  @description: performs required field validation
	*/
	$('form#post').on("submit", function(){
		
		// do validation
		do_validation();
		
		if( rwmb.isValid == false)
		{
			// reset validation for next time
			rwmb.isValid = true;
			
			// show message
			$(this).siblings('#message').remove();
			$(this).before('<div id="message" class="error"><p>' + rwmb.validation_message + '</p></div>');
			
			
			// hide ajax stuff on submit button
			$('#publish').removeClass('button-primary-disabled');
			$('#ajax-loading').attr('style','');
			return false;
		}
		
		$('.rwmb_postbox:hidden').remove();
		
		// submit the form
		return true;
		
	});
	
	function do_validation(){
		
		var isValid = true;
		
		$('.rwmb-field.required').each(function(){
			
			// text / textarea
			if($(this).find('input[type="text"], input[type="hidden"], textarea').val() == "")
			{
				isValid = false;
			}
			
			// select
			if(isValid && $(this).find('select').exists())
			{
				if($(this).find('select').val() == "null" || !$(this).find('select').val() == "" || !$(this).find('select').val())
				{
					isValid = false;
				}
			}
			
			// checkbox
			if(isValid && $(this).find('input[type="checkbox"]:checked').exists())
			{
				isValid = true;
			}

			// set validation
			if(!isValid)
			{
				$(this).closest('.rwmb-field').addClass('error');
			}
			
		});
		
		rwmb.isValid = isValid;
		
	}
	
	/*
	*  Remove error class on focus
	*/
	// inputs / textareas
	$('.rwmb-field input, .rwmb-field.required textarea, .rwmb-field.required select').on('focus', function(){
		$(this).closest('.rwmb-field').removeClass('error');
	});
	
	// checkbox
	$('.rwmb-field.required input:checkbox').on('click', function(){
		$(this).closest('.rwmb-field').removeClass('error');
	});
	
})(jQuery);