( function ( $, _, rwmb ) {
	'use strict';

	/**
	 * Show preview of oembeded media.
	 */
	function showPreview() {
		var $this = $( this ),
			$spinner = $this.siblings( '.spinner' ),
			data = {
				action: 'rwmb_get_embed',
				url: this.value,
				_ajax_nonce: rwmbOembed.nonce,
				not_available: $this.data( 'not-available' ),
			};

		$spinner.css( 'visibility', 'visible' );
		$.post( ajaxurl, data, function ( response ) {
			$spinner.css( 'visibility', 'hidden' );
			$this.siblings( '.rwmb-embed-media' ).html( response.data );
		}, 'json' );
	}

	/**
	 * Remove oembed preview when cloning.
	 */
	function removePreview() {
		$( this ).siblings( '.rwmb-embed-media' ).html( '' );
	}

	function reset( e ) {
		$( e.target ).find( '.rwmb-oembed' ).each( function () {
			( this.value ? showPreview : removePreview ).call( this );
		} );
	}

	rwmb.$document
		.on( 'mb_reset', reset )
		.on( 'change', '.rwmb-oembed', _.debounce( showPreview, 250 ) )
	    .on( 'clone', '.rwmb-oembed', removePreview );
} )( jQuery, _, rwmb );
