jQuery(document).ready(function($) {
	$('.rw-time').each(function(){
		var $this = $(this),
			format = $this.attr('rel');

		$this.timepicker({
			showSecond: true,
			timeFormat: format
		});
	});
});