( function ( $, rwmb ) {
	'use strict';

	var frame;

	function openSelectPopup( e ) {
		e.preventDefault();
		var $el = $( this );

		// Create a frame only if needed
		if ( ! frame ) {
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
		frame.on( 'select', function () {
			var url = frame.state().get( 'selection' ).first().toJSON().url;
			$el.siblings( 'input' ).val( url ).siblings( 'a' ).removeClass( 'hidden' );
		} );
	}

	function clearSelection( e ) {
		e.preventDefault();
		$( this ).addClass( 'hidden' ).siblings( 'input' ).val( '' );
	}

	function hideRemoveButtonWhenCloning() {
		$( this ).siblings( '.rwmb-file-input-remove' ).addClass( 'hidden' );
	}

	rwmb.$document
		.on( 'click', '.rwmb-file-input-select', openSelectPopup )
		.on( 'click', '.rwmb-file-input-remove', clearSelection )
		.on( 'clone', '.rwmb-file_input', hideRemoveButtonWhenCloning );
} )( jQuery, rwmb );
