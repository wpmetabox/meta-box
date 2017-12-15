jQuery( function ( $ ) {
	'use strict';
		if ($('.rwmb-upload-image').length > 0) {
			if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
				$(document).on('click', '.rwmb-upload-image', function(e) {
					e.preventDefault();
					var button = $(this),
						parent = button.parent();;
					wp.media.editor.send.attachment = function(props, attachment) {
						$('.rwmb-upload-background', parent).val(attachment.url);
					};
					wp.media.editor.open(button);
					
					return false;
				});
			}
		}
} );
