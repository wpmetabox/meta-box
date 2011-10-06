jQuery(document).ready(function($) {
	// reorder images
	$('.rw-images').each(function(){
		var $this = $(this),
			order, data;
		$this.sortable({
			placeholder: 'ui-state-highlight',
			update: function (){
				order = $this.sortable('serialize');
				data = order + '|' + $this.siblings('.rw-images-data').val();

				$.post(ajaxurl, {action: 'rw_reorder_images', data: data}, function(response){
					response == '0' ? alert('Order saved') : alert("You don't have permission to reorder images.");
				});
			}
		});
	});

	// thickbox upload
	$('.rw-upload-button').click(function(){
		var data = $(this).attr('rel').split('|'),
			post_id = data[0],
			field_id = data[1],
			backup = window.send_to_editor;		// backup the original 'send_to_editor' function which adds images to the editor

		// change the function to make it adds images to our section of uploaded images
		window.send_to_editor = function(html) {
			$('#rw-images-' + field_id).append($(html));

			tb_remove();
			window.send_to_editor = backup;
		};

		// note that we pass the field_id and post_id here
		tb_show('', 'media-upload.php?post_id=' + post_id + '&field_id=' + field_id + '&type=image&TB_iframe=true');

		return false;
	});

	// add checkboxes to select images to add
	$('#media-items .new').each(function() {
		var id = $(this).parent().attr('id').split('-')[2];
		$(this).prepend('<input type="checkbox" class="item_selection" id="attachments[' + id + '][selected]" name="attachments[' + id + '][selected]" value="selected" /> ');
	});

	// add checkboxes to select images to add
	$('.ml-submit').live('mouseenter',function() {
		$('#media-items .new').each(function() {
			var id = $(this).parent().children('input[value="image"]').attr('id');
			if (!id) return;
			id = id.split('-')[2];
			$(this).not(':has("input")').prepend('<input type="checkbox" class="item_selection" id="attachments[' + id + '][selected]" name="attachments[' + id + '][selected]" value="selected" /> ');
		});
	});

	// add 'Insert selected images' button
	// we need to pull out the 'field_id' from the url as the media uploader is an iframe
	var field_id = get_query_var('field_id');
	$('.ml-submit:first').append('<input type="hidden" name="field_id" value="' + field_id + '" /> <input type="submit" class="button" name="rw-insert" value="Insert selected images" />');

	// helper function
	// get query string value by name, http://goo.gl/r0CH5
	function get_query_var(name) {
		var match = RegExp('[?&]' + name + '=([^&#]*)').exec(location.href);

		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	}
});