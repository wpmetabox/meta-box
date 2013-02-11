jQuery( document ).ready( function( $ )
{
	var rwmb_file_frames = {};
	
	$( '.rwmb-file-advanced-upload' ).on( 'click', function(e) {
		e.preventDefault();
		
		var upload_button = $(this),
			file_list = upload_button.siblings( '.rwmb-uploaded' ),
			field_id = file_list.data('field_id'),
			max_file_uploads = file_list.data( 'max_file_uploads' ),
			mime_type = file_list.data( 'mime_type' );

		// If the frame already exists, re-open it.
        if ( rwmb_file_frames[field_id] ) {
            rwmb_file_frames[field_id].open();
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
		
		if( mime_type ) {
			frame_options.library = {
				type : mime_type
			};	
		}
		
		rwmb_file_frames[field_id] = wp.media( frame_options );		
		
		//Handle selection
		rwmb_file_frames[field_id].on( 'select', function() {
			//Get selections
			var selection = rwmb_file_frames[field_id].state().get( 'selection' ),
				msg = 'You may only upload ' + max_file_uploads + ' file',
				uploaded = file_list.children().length,
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
				if( file_list.children('li#item_' + attachment.id ).length > 0 || max_file_uploads > 0 && uploaded + 1 + index > max_file_uploads){					
					return;
				}				
				
				//Attach attachment to field and get HTML
				var data = {
					action			: 'rwmb_attach_file',
					post_id			: $( '#post_ID' ).val(),
					field_id		: file_list.data('field_id'),
					attachment_id	: attachment.id,
					_wpnonce		: upload_button.data('attach_file_nonce')
				};
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
					else
						file_list.removeClass( 'hidden' ).append( res.responses[0].data );
				}, 'xml' );
			});
		});
		
		
		//Open
		rwmb_file_frames[field_id].open();
			
	} );
	
	$( '.rwmb-uploaded ' ).on( 'click', '.rwmb-delete-file', function()
	{
		$( this ).parents( '.rwmb-uploaded' ).siblings( '.rwmb-file-advanced-upload' ).removeClass( 'hidden' );		
	} );
		
} );