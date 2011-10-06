jQuery(document).ready(function($) {
	// Add more file
	$('.rw-add-file').click(function() {
		var $first = $(this).parent().find('.file-input:first');
		$first.clone().insertAfter($first).show();
		return false;
	});

	// Delete file via Ajax
	$('.rw-upload').delegate('.rw-delete-file', 'click', function() {
		var $this = $(this),
			$parent = $this.parent(),
			data = $this.attr('rel');
		$.post(ajaxurl, {action: 'rw_delete_file', data: data}, function(response) {
			response == '0' ? (alert('File has been successfully deleted.'),$parent.remove()) : alert('You do NOT have permission to delete this file.');
		});
		return false;
	});
});