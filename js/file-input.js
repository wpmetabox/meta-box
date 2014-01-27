jQuery( function( $ )
{
	var frame;

	$( 'body' ).on( 'click', '.rwmb-file-input-select', function( e )
	{
		e.preventDefault();
		var $el = $( this );

		// Create a frame only if needed
		if ( !frame )
		{
			frame = wp.media( {
				className: 'media-frame rwmb-file-frame',
				multiple: false,
				title: rwmbFileInput.frameTitle
			} );
		}

		// Open media uploader
		frame.open();

		// Remove all attached 'select' event
		frame.off( 'select' );

		// Handle selection
		frame.on( 'select', function()
		{
			var url = frame.state().get( 'selection' ).first().toJSON().url;
			$el.siblings( 'input' ).val( url ).siblings( 'a' ).removeClass( 'hidden' );
		} );
	} );

	// Clear selected images
	$( 'body' ).on( 'click', '.rwmb-file-input-remove', function ( e )
	{
		e.preventDefault();
		$( this ).addClass( 'hidden' ).siblings( 'input' ).val( '' );
	} );
} );
