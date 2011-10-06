jQuery(document).ready(function($) {
	$('.rw-date').each(function() {
		var $this = $(this),
			format = $this.attr('rel');

		$this.datepicker({
			showButtonPanel: true,
			dateFormat: format
		});
	});
});