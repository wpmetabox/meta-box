jQuery( document ).ready( function( $ )
{
	$( '.rwmb-file-advanced-upload' ).each(function(){
		var $uploadButton = $(this),
			$fileList = $uploadButton.siblings( '.rwmb-uploaded' ),
			fieldID = $fileList.data('field_id'),
			maxFileUploads = $fileList.data( 'max_file_uploads' ),
			mimeType = $fileList.data( 'mime_type' ),
			frameOptions = {
				className	: 'media-frame rwmb-file-frame',
				frame		: 'select',
				multiple	: true,
				title		: 'Select files',
				button		: {
					text		:	'Select'
				}
			},
			rwmbFileFrame;
			
		if( mimeType ) {
			frameOptions.library = {
				type : mimeType
			};	
		}
		//Setup media frame
		rwmbFileFrame = wp.media( frameOptions );	
		
		//Button click handler
		$uploadButton.on( 'click', function(e) {
			e.preventDefault();
			rwmbFileFrame.open();
		} );
		
		//File select handler
		rwmbFileFrame.on( 'select', function() {
			//Get selections
			var selection = rwmbFileFrame.state().get( 'selection' ).toJSON(),
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
	});
} );