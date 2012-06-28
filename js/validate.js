jQuery(function ($) {

	$(document).ready(function() {
	
		// required field styling
		$.each(rwmb.validationOptions.rules, function(k, v) {
			if (v['required']) {
				var label = $('#' + k).parent().siblings('.rwmb-label');
				$(label).find('label').css('font-weight','bold');
				$(label).append('<span class="required">*</span>');
			}
		});
		
		rwmb.validationOptions.invalidHandler = function(form, validator) {
			// re-enable the submit (publish/update) button and hide the ajax indicator
			$('#publish').removeClass('button-primary-disabled');
			$('#ajax-loading').attr('style','');
			$('form#post').siblings('#message').remove();
			$('form#post').before('<div id="message" class="error"><p>' + rwmb.summaryMessage  + '</p></div>');
		};
		$('form#post').validate(rwmb.validationOptions);
		
	});
	
});