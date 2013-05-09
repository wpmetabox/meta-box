jQuery( function( $ )
{
	// Use only one frame for all upload fields
	var frame;

	$( 'body' ).on( 'click', '.rwmb-file-advanced-upload', function( e )
	{
		e.preventDefault();

		var $uploadButton = $( this ),
			$fileList = $uploadButton.siblings( '.rwmb-uploaded' ),
			fieldID = $fileList.data( 'field_id' ),
			maxFileUploads = $fileList.data( 'max_file_uploads' ),
			mimeType = $fileList.data( 'mime_type' ),
			msg = 'You may only upload ' + maxFileUploads + ' file';

		if ( maxFileUploads > 1 )
			msg += 's';

		// Create a frame only if needed
		if ( !frame )
		{
			var frameOptions = ( {
				className	: 'media-frame rwmb-file-frame',
				multiple	: true,
				title		: 'Select files'
			} );

			if ( mimeType )
			{
				frameOptions.library = {
					type : mimeType
				};
			}

			frame = wp.media( frameOptions );
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
				uploaded = $fileList.children().length,
				ids;

			if ( maxFileUploads > 0 && ( uploaded + selection.length ) > maxFileUploads )
			{
				if ( uploaded < maxFileUploads )
					selection = selection.slice( 0, maxFileUploads - uploaded );
				alert( msg );
			}
			
			ids = $.map( selection, function( attachment )
			{
				if ( $fileList.children( 'li#item_' + attachment.id ).length > 0 )
					return;
				return attachment.id;
			} )
			
			if( ids.length > 0 )
			{
				// Attach attachment to field and get HTML
				var data = {
					action       : 'rwmb_attach_file',
					post_id      : $( '#post_ID' ).val(),
					field_id     : $fileList.data( 'field_id' ),
					attachment_ids: ids,
					_ajax_nonce  : $uploadButton.data( 'attach_file_nonce' )
				};
				$.post( ajaxurl, data, function( r )
				{
					if( r.success )
					{
						$fileList
							.append( _.template( $( '#tmpl-rwmb-file-advanced' ).html(),  { attachments: selection } ) )
							.trigger('update.rwmbFile');;
					}
				}, 'json' );
			}
		} );
	} );
} );
