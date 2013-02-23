jQuery( document ).ready( function( $ )
{
	var rwmbFileFrames = {};
	
	$( '.rwmb-file-advanced-upload' ).on( 'click', function(e) {
		e.preventDefault();
		
		var uploadButton = $(this),
			$fileList = uploadButton.siblings( '.rwmb-uploaded' ),
			fieldID = $fileList.data('field_id'),
			maxFileUploads = $fileList.data( 'max_file_uploads' ),
			mimeType = $fileList.data( 'mime_type' );
		console.log($fileList);

		// If the frame already exists, re-open it.
        if ( rwmbFileFrames[fieldID] ) {
            rwmbFileFrames[fieldID].open();
            return;
        }
		//Setup media frame
		frame_options = {
            className	: 'media-frame rwmb-file-frame',
            frame		: 'select',
            multiple	: true,
            title		: 'Select files',
            button		: {
                text		:	'Select'
            }
        };
		
		if( mimeType ) {
			frame_options.library = {
				type : mimeType
			};	
		}
		
		rwmbFileFrames[fieldID] = wp.media( frame_options );		
		
		//Handle selection
		rwmbFileFrames[fieldID].on( 'select', function() {
			//Get selections
			var selection = rwmbFileFrames[fieldID].state().get( 'selection' ),
				msg = 'You may only upload ' + maxFileUploads + ' file',
				uploaded = $fileList.children().length,
				total = uploaded + selection.length;
			if ( maxFileUploads > 1 )
				msg += 's';
			
			if( maxFileUploads > 0 ) {
				if( total > maxFileUploads ) {
					alert( msg );
				}
				if( total >= maxFileUploads ) {
					uploadButton.addClass( 'hidden' );
				}
			}
				
			selection.map( function( attachment, index ) {
				
				//Convert attachment to JSON			 
				attachment = attachment.toJSON();

				//Check if image already attached
				if( $fileList.children('li#item_' + attachment.id ).length > 0 || maxFileUploads > 0 && uploaded + 1 + index > maxFileUploads){					
					return;
				}				
				
				//Attach attachment to field and get HTML
				var data = {
					action			: 'rwmb_attach_file',
					post_id			: $( '#post_ID' ).val(),
					field_id		: $fileList.data('field_id'),
					attachment_id	: attachment.id,
					_ajax_nonce		: uploadButton.data('attach_file_nonce')
				};
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
					else
						$fileList.append( res.responses[0].data );
				}, 'xml' );
			});
		});
		
		
		//Open
		rwmbFileFrames[fieldID].open();
			
	} );
	
	$( '.rwmb-uploaded ' ).on( 'click', '.rwmb-delete-file', function()
	{
		$( this ).parents( '.rwmb-uploaded' ).siblings( '.rwmb-file-advanced-upload' ).removeClass( 'hidden' );		
	} );
} );