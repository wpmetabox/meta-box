jQuery( function ( $ )
{
	'use strict';

	/**
	 * Update color picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update()
	{
		var $this = $( this ),
			$container = $this.closest( '.rwmb-color-clone' );

		// Clone doesn't have input for color picker, we have to add the input and remove the color picker container
		if ( $container.length > 0 )
		{
			$this.appendTo( $container ).siblings( '.wp-picker-container' ).remove();
		}

		// Show color picker
		$this.wpColorPicker( $this.data( 'options' ) );
	}

	$( ':input.rwmb-color' ).each( update );
	$( '.rwmb-input' ).on( 'clone', 'input.rwmb-color', update );
} );
