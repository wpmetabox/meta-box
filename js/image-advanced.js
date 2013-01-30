jQuery( document ).ready( function( $ )
{
	var rwmb_media_frame,
		rwmb_image_list;
	$( '.rwmb-image-advanced-upload' ).on( 'click', function(e) {
		var upload_button = $(this);
		rwmb_image_list = $(this).siblings( '.rwmb-images' );
		e.preventDefault();
		
		// If the frame already exists, re-open it.
        if ( rwmb_media_frame ) {
            rwmb_media_frame.open();
            return;
        }
		//Create media frame
		rwmb_media_frame = wp.media.frames.rwmb_media_frame = wp.media({

            className: 'media-frame rwmb-media-frame',
            frame: 'select',
            multiple: true,
            title: 'Upload Images',
            library: {
                type: 'image'
            },
            button: {
                text:  'Select'
            }
        } );
		//Handle selection
		rwmb_media_frame.on( 'select', function() {
			//Get selections
			var selection = rwmb_media_frame.state().get( 'selection' );
			selection.map( function( attachment, index ) {
				console.log(index);
				//Convert attachment to JSON			 
				attachment = attachment.toJSON();
				//Attach attachment to field and get HTML
				var data = {
					action			: 'rwmb_attach_media',
					post_id			: $( '#post_ID' ).val(),
					field_id		: rwmb_image_list.data('field_id'),
					attachment_id	: attachment.id,
					_wpnonce		: upload_button.data('attach_media_nonce')
				};
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
					else
					{
						rwmb_image_list.removeClass( 'hidden' ).prepend( res.responses[0].data );
					}
				}, 'xml' );
			});
		});
		
		//Open
		rwmb_media_frame.open();
	} );
} );