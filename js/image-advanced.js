jQuery( document ).ready( function( $ )
{
	var rwmbMediaFrame;

	$( '.rwmb-image-advanced-upload' ).on( 'click', function(e) {
		e.preventDefault();
		
		var upload_button = $(this),
			$imageList = $(this).siblings( '.rwmb-images' ),
			maxFileUploads = $imageList.data( 'max_file_uploads' );
		
		
		// If the frame already exists, re-open it.
        if ( rwmbMediaFrame ) {
            rwmbMediaFrame.open();
            return;
        }
		//Create media frame
		frameOptions = {
            className	: 'media-frame rwmb-media-frame',
            frame		: 'select',
            multiple	: true,
            title		: 'Select or Upload Images',
            library		: {
            	type		:	'image'
            },
            button		: {
                text		:	'Select'
            }
        } ;
		
		rwmbMediaFrame = wp.media.frames.rwmbMediaFrame = wp.media( frameOptions );
		
		//Handle selection
		rwmbMediaFrame.on( 'select', function() {
			//Get selections
			var selection = rwmbMediaFrame.state().get( 'selection' ),
				msg = 'You may only upload ' + maxFileUploads + ' file',
				uploaded = $imageList.children().length,
				total = uploaded + selection.length;
			if ( maxFileUploads > 1 )
				msg += 's';
			
			if( maxFileUploads > 0 ) {
				if( total > maxFileUploads ) {
					alert( msg );
				}
				if( total >= maxFileUploads ) {
					upload_button.addClass( 'hidden' );
				}
			}

			selection.map( function( attachment, index ) {
				
				//Convert attachment to JSON			 
				attachment = attachment.toJSON();

				//Check if image already attached
				if( $imageList.children('li#item_' + attachment.id ).length > 0 || maxFileUploads > 0 && uploaded + 1 + index > maxFileUploads){					
					return;
				}				
				
				//Attach attachment to field and get HTML
				var data = {
					action			: 'rwmb_attach_media',
					post_id			: $( '#post_ID' ).val(),
					field_id		: $imageList.data('field_id'),
					attachment_id	: attachment.id,
					_ajax_nonce		: upload_button.data('attach_media_nonce')
				};
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
					else
						$imageList.prepend( res.responses[0].data );
				}, 'xml' );
			});
		});		
		
		//Open
		rwmbMediaFrame.open();
	} );
	
	$( '.rwmb-images' ).on( 'click', '.rwmb-delete-file', function()
	{
		$( this ).parents( '.rwmb-images' ).siblings( '.rwmb-image-advanced-upload' ).removeClass( 'hidden' );		
	} );
} );