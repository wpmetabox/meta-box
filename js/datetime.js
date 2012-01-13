jQuery(document).ready(function($) {
	$('.rwmb-datetime').each(function(){
		var $this = $(this),
			format = $this.attr('rel');

		$this.datetimepicker({
			showSecond: true,
			timeFormat: format
		});
	});
});