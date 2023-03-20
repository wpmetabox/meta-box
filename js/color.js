( function( $, rwmb ) {
	'use strict';

	/**
	 * Transform an input into a color picker.
	 */
	function transform() {
		const $this = $( this );
		const mode = $this.data( 'options' )[ 'mode' ];
		const alpha = $this.data( 'alpha-enabled' );

		function initChange() {
			if ( null !== mode && 'hex' !== mode && !alpha ) {
				const color = new Color( $this.iris( 'option', 'color' ) );
				$this.val( color.toCSS( mode ) );
			}
			triggerChange();
		}

		function triggerChange() {
			$this.trigger( 'color:change' ).trigger( 'mb_change' );
		}

		const $container = $this.closest( '.wp-picker-container' ),
			// Hack: the picker needs a small delay (learn from the Kirki plugin).
			options = $.extend(
				{
					change: function() {
						setTimeout( initChange, 20 );
					},
					clear: function() {
						setTimeout( triggerChange, 20 );
					}
				},
				$this.data( 'options' )
			);

		// Clone doesn't have input for color picker, we have to add the input and remove the color picker container
		if ( $container.length > 0 ) {
			$this.insertBefore( $container );
			$container.remove();
		}

		// Show color picker.
		$this.wpColorPicker( options );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-color' ).each( transform );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-color', transform );
} )( jQuery, rwmb );
