jQuery( document ).ready( function( $ )
{
	var rwmb_media_frame;

	$( '.rwmb-image-advanced-upload' ).on( 'click', function(e) {
		e.preventDefault();
		
		var upload_button = $(this),
			image_list = $(this).siblings( '.rwmb-images' ),
			max_file_uploads = image_list.data( 'max_file_uploads' );
		
		
		// If the frame already exists, re-open it.
        if ( rwmb_media_frame ) {
            rwmb_media_frame.open();
            return;
        }
		//Create media frame
		frame_options = {
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
		
		rwmb_media_frame = wp.media.frames.rwmb_media_frame = wp.media( frame_options );
		
		//Handle selection
		rwmb_media_frame.on( 'select', function() {
			//Get selections
			var selection = rwmb_media_frame.state().get( 'selection' ),
				msg = 'You may only upload ' + max_file_uploads + ' file',
				uploaded = image_list.children().length,
				total = uploaded + selection.length;
			if ( max_file_uploads > 1 )
				msg += 's';
			
			if( max_file_uploads > 0 ) {
				if( total > max_file_uploads ) {
					alert( msg );
				}
				if( total >= max_file_uploads ) {
					upload_button.addClass( 'hidden' );
				}
			}

			selection.map( function( attachment, index ) {
				
				//Convert attachment to JSON			 
				attachment = attachment.toJSON();

				//Check if image already attached
				if( image_list.children('li#item_' + attachment.id ).length > 0 || max_file_uploads > 0 && uploaded + 1 + index > max_file_uploads){					
					return;
				}				
				
				//Attach attachment to field and get HTML
				var data = {
					action			: 'rwmb_attach_media',
					post_id			: $( '#post_ID' ).val(),
					field_id		: image_list.data('field_id'),
					attachment_id	: attachment.id,
					_wpnonce		: upload_button.data('attach_media_nonce')
				};
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
					else
						image_list.removeClass( 'hidden' ).prepend( res.responses[0].data );
				}, 'xml' );
			});
		});		
		
		//Open
		rwmb_media_frame.open();
	} );
	
	$( '.rwmb-images' ).on( 'click', '.rwmb-delete-file', function()
	{
		$( this ).parents( '.rwmb-images' ).siblings( '.rwmb-image-advanced-upload' ).removeClass( 'hidden' );		
	} );
} );