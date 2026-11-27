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

	function reset( e ) {
		$( e.target ).find( '.rwmb-color' ).each( function () {
			const $this = $( this );
			if ( this.value ) {
				$this.wpColorPicker( 'color', this.value );
				return;
			}

			$this.closest( '.wp-picker-container' ).find( '.wp-color-result, .color-alpha' ).css( 'background-color', '' );
			const iris = $this.iris( 'instance' );
			if ( iris ) {
				// Iris ignores empty colors in option('color', ''), so reset state without triggering change or writing the input.
				iris._color = new Color( '' ).setHSpace( iris.options.mode );
				iris.hue = 0;
				iris.active = 'external';
				iris._inited = false;
				iris._change();
			}
		} );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'mb_reset', reset )
		.on( 'clone', '.rwmb-color', transform );
} )( jQuery, rwmb );
