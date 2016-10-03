jQuery( function ( $ ) {
	'use strict';

	/**
	 * Show preview of oembeded media.
	 */
	function showPreview( e ) {
		e.preventDefault();

		var $this = $( this ),
			$spinner = $this.siblings( '.spinner' ),
			data = {
				action: 'rwmb_get_embed',
				url: $this.siblings( 'input' ).val()
			};

		$spinner.css( 'visibility', 'visible' );
		$.post( ajaxurl, data, function ( r ) {
			$spinner.css( 'visibility', 'hidden' );
			$this.siblings( '.rwmb-embed-media' ).html( r.data );
		}, 'json' );
	}

	/**
	 * Remove oembed preview when cloning.
	 */
	function removePreview() {
		$( this ).siblings( '.rwmb-embed-media' ).html( '' );
	}

	// Show oembeded media when clicking "Preview" button
	$( 'body' ).on( 'click', '.rwmb-embed-show', showPreview );

	// Remove oembed preview when cloning
	$( '.rwmb-input' ).on( 'clone', '.rwmb-oembed', removePreview );
} );
