jQuery(document).ready(function($) {
	$('.rw-color-picker').each(function() {
		var $this = $(this),
			id = $this.attr('rel');

		$this.farbtastic('#' + id);
	});

	$('.rw-color-select').click(function() {
		$(this).siblings('.rw-color-picker').toggle();
		return false;
	});
});