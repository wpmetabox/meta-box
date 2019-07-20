( function ( $, rwmb ) {
	'use strict';

	/**
	 * Transform an input into a color picker.
	 */
	function transform() {
		var $this = $( this ),
			$container = $this.closest( '.wp-picker-container' ),
			data = $.extend(
				{
					change: function () {
						$( this ).trigger( 'color:change' );
					},
					clear: function () {
						$( this ).trigger( 'color:clear' );
					}
				},
				$this.data( 'options' )
			);

		// Clone doesn't have input for color picker, we have to add the input and remove the color picker container
		if ( $container.length > 0 ) {
			$this.insertBefore( $container );
			$container.remove();
		}

		// Show color picker
		$this.wpColorPicker( data );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-color' ).each( transform );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-color', transform );
} )( jQuery, rwmb );
