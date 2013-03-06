jQuery( document ).ready( function( $ )
{
	$( '.rwmb-image-advanced-upload' ).each(function(){
		var $uploadButton = $(this),
			$imageList = $(this).siblings( '.rwmb-images' ),
			maxFileUploads = $imageList.data( 'max_file_uploads' ),
			frameOptions = {
				className	: 'media-frame rwmb-media-frame',
				frame		: 'select',
				multiple	: true,
				title		: 'Select or Upload Media',
				library		: {
					type		:	'image'
				},
				button		: {
					text		:	'Select'
				}
			},
			rwmbMediaFrame = wp.media( frameOptions ) ;
			
		//Button click handler
		$uploadButton.on( 'click', function(e) {
			e.preventDefault();
			rwmbMediaFrame.open();
		} );
			
		//Handle selection
		rwmbMediaFrame.on( 'select', function() {
			console.log($imageList);
			//Get selections
			var selection = rwmbMediaFrame.state().get( 'selection' ).toJSON(),
				msg = 'You may only upload ' + maxFileUploads + ' file',
				uploaded = $imageList.children().length;
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
				if( $imageList.children('li#item_' + attachment.id ).length > 0  ){					
					continue;
				}								
				
				//Attach attachment to field and get HTML
				var data = {
					action			: 'rwmb_attach_media',
					post_id			: $( '#post_ID' ).val(),
					field_id		: $imageList.data('field_id'),
					attachment_id	: attachment.id,
					_ajax_nonce		: $uploadButton.data('attach_media_nonce')
				};
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
					else
						$imageList.removeClass('hidden').prepend( res.responses[0].data );
					
					// Hide files button if reach max file uploads
					if ( $imageList.children().length >= maxFileUploads ) $uploadButton.addClass( 'hidden' );
				}, 'xml' );	
			}			
		});	
			
	});
} );