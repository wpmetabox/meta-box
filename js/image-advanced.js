jQuery( function( $ )
{
	// Use only one frame for all upload fields
	var frame;

	$( 'body' ).on( 'click', '.rwmb-image-advanced-upload', function( e )
	{
		e.preventDefault();

		var $uploadButton = $( this ),
			$imageList = $uploadButton.siblings( '.rwmb-images' ),
			maxFileUploads = $imageList.data( 'max_file_uploads' ),
			msg = 'You may only upload ' + maxFileUploads + ' file';

		if ( maxFileUploads > 1 )
			msg += 's';

		// Create a frame only if needed
		if ( !frame )
		{
			frame = wp.media( {
				className: 'media-frame rwmb-media-frame',
				multiple : true,
				title    : 'Select or Upload Media',
				library  : {
					type: 'image'
				}
			} );
		}

		// Open media uploader
		frame.open();

		// Remove all attached 'select' event
		frame.off( 'select' );

		// Handle selection
		frame.on( 'select', function()
		{
			// Get selections
			var selection = frame.state().get( 'selection' ).toJSON(),
				uploaded = $imageList.children().length;

			if ( maxFileUploads > 0 && ( uploaded + selection.length ) > maxFileUploads )
			{
				if ( uploaded < maxFileUploads )
					selection = selection.slice( 0, maxFileUploads - uploaded );
				alert( msg );
			}

			for ( var i in  selection )
			{
				var attachment = selection[i];

				// Check if image already attached
				if ( $imageList.children( 'li#item_' + attachment.id ).length > 0 )
					continue;

				// Attach attachment to field and get HTML
				var data = {
					action       : 'rwmb_attach_media',
					post_id      : $( '#post_ID' ).val(),
					field_id     : $imageList.data( 'field_id' ),
					attachment_id: attachment.id,
					_ajax_nonce  : $uploadButton.data( 'attach_media_nonce' )
				};
				$.post( ajaxurl, data, function( r )
				{
					var r = wpAjax.parseAjaxResponse( r, 'ajax-response' );

					if ( r.errors )
						alert( r.responses[0].errors[0].message );
					else
						$imageList.removeClass( 'hidden' ).prepend( r.responses[0].data );

					// Hide files button if reach max file uploads
					if ( $imageList.children().length >= maxFileUploads )
						$uploadButton.addClass( 'hidden' );
				}, 'xml' );
			}
		} );
	} );
} );
