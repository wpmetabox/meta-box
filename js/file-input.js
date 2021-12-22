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
			var url             = frame.state().get( 'selection' ).first().toJSON().url;
			var fileType        =  url.split( '.' ).pop().toLowerCase();
			var validImageTypes = [ 'gif', 'jpeg', 'png', 'jpg' ];
			$el.siblings( 'input' ).val( url ).trigger( 'change' ).siblings( 'a' ).removeClass( 'hidden' );
			if ( validImageTypes.includes( fileType ) ) {
				$el.closest( '.rwmb-file-input-inner' ).siblings( '.rwmb-file-input-image' ).removeClass( 'hidden' ).html( '<img src="'+ url +'">' ) ;
			} else {
				$el.closest( '.rwmb-file-input-inner' ).siblings( '.rwmb-file-input-image' ).addClass( 'hidden' );
			}
		} );
	}

	function clearSelection( e ) {
		e.preventDefault();
		$( this ).addClass( 'hidden' ).siblings( 'input' ).val( '' ).trigger( 'change' );
		$( this ).closest( '.rwmb-file-input-inner' ).siblings( '.rwmb-file-input-image' ).addClass( 'hidden' );
	}

	function hideRemoveButtonWhenCloning() {
		$( this ).siblings( '.rwmb-file-input-remove' ).addClass( 'hidden' );
	}

	rwmb.$document
		.on( 'click', '.rwmb-file-input-select', openSelectPopup )
		.on( 'click', '.rwmb-file-input-remove', clearSelection )
		.on( 'clone', '.rwmb-file_input', hideRemoveButtonWhenCloning );
} )( jQuery, rwmb );
