jQuery( function ( $ )
{
	'use strict';

	/**
	 * Show color pickers
	 * @return void
	 */
	function initColorPicker()
	{
		var $this = $( this ),
			$container = $this.closest( '.rwmb-color-clone' );

		// Clone doesn't have input for color picker, we have to add the input and remove the color picker container
		if ( $container.length > 0 )
		{
			$this.appendTo( $container ).siblings( '.wp-picker-container' ).remove();
		}

		// Make sure the value is displayed
		if ( !$this.val() )
		{
			$this.val( '#' );
		}

		// Show color picker
		$this.wpColorPicker( $this.data( 'options' ) );
	}

	$( ':input.rwmb-color' ).each( initColorPicker );
	$( '.rwmb-input' ).on( 'clone', 'input.rwmb-color', initColorPicker );
} );
