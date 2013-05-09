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
				uploaded = $imageList.children().length,
				ids;

			if ( maxFileUploads > 0 && ( uploaded + selection.length ) > maxFileUploads )
			{
				if ( uploaded < maxFileUploads )
					selection = selection.slice( 0, maxFileUploads - uploaded );
				alert( msg );
			}
			
			ids = $.map( selection, function( attachment )
			{
				if ( $imageList.children( 'li#item_' + attachment.id ).length > 0 )
					return;
				return attachment.id;
			} )

			if( ids.length > 0 )
			{
				var data = {
					action			: 'rwmb_attach_media',
					post_id			: $( '#post_ID' ).val(),
					field_id		: $imageList.data( 'field_id' ),
					attachment_ids	: ids,
					_ajax_nonce  	: $uploadButton.data( 'attach_media_nonce' )
				};

				$.post( ajaxurl, data, function( r )
				{
					if( r.success )
					{
						$imageList
							.append( _.template( $( '#tmpl-rwmb-image-advanced' ).html(), { attachments: selection } ) )
							.trigger('update.rwmbFile');						
					}					
				}, 'json' );
			}
		} );
	} )
} );
