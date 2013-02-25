jQuery( document ).ready( function( $ )
{
	var rwmbFileFrames = {};
	
	$( '.rwmb-file-advanced-upload' ).on( 'click', function(e) {
		e.preventDefault();
		
		var $uploadButton = $(this),
			$fileList = $uploadButton.siblings( '.rwmb-uploaded' ),
			fieldID = $fileList.data('field_id'),
			maxFileUploads = $fileList.data( 'max_file_uploads' ),
			mimeType = $fileList.data( 'mime_type' );

		// If the frame already exists, re-open it.
        if ( rwmbFileFrames[fieldID] ) {
            rwmbFileFrames[fieldID].open();
            return;
        }
		//Setup media frame
		frameOptions = {
            className	: 'media-frame rwmb-file-frame',
            frame		: 'select',
            multiple	: true,
            title		: 'Select files',
            button		: {
                text		:	'Select'
            }
        };
		
		if( mimeType ) {
			frameOptions.library = {
				type : mimeType
			};	
		}
		
		rwmbFileFrames[fieldID] = wp.media( frameOptions );		
		
		//Handle selection
		rwmbFileFrames[fieldID].on( 'select', function() {
			//Get selections
			var selection = rwmbFileFrames[fieldID].state().get( 'selection' ).toJSON(),
				msg = 'You may only upload ' + maxFileUploads + ' file',
				uploaded = $fileList.children().length;
			if ( maxFileUploads > 1 )
				msg += 's';
			
			if ( maxFileUploads > 0  && ( uploaded + selection.length ) > maxFileUploads )
			{
				if( uploaded < maxFileUploads ){
					selection = selection.slice( 0, maxFileUploads - uploaded );
				}
				alert( msg );				
			}
			for( i in  selection) {
				var attachment = selection[i];
				
				//Check if image already attached
				if( $fileList.children('li#item_' + attachment.id ).length > 0  ){					
					continue;
				}								
				
				var data = {
					action			: 'rwmb_attach_file',
					post_id			: $( '#post_ID' ).val(),
					field_id		: $fileList.data('field_id'),
					attachment_id	: attachment.id,
					_ajax_nonce		: $uploadButton.data('attach_file_nonce')
				};
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
					else 
						$fileList.removeClass('hidden').prepend( res.responses[0].data );
					
					// Hide files button if reach max file uploads
					if ( $fileList.children().length >= maxFileUploads ) $uploadButton.addClass( 'hidden' );
				}, 'xml' );	
			}
		});
		
		
		//Open
		rwmbFileFrames[fieldID].open();
			
	} );
} );