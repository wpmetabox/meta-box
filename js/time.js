jQuery( function ( $ )
{
	'use strict';

	/**
	 * Update datetime picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update()
	{
		var $this = $( this ),
			options = $this.data( 'options' );

		$this.siblings( '.ui-datepicker-append' ).remove();  // Remove appended text
		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).timepicker( options );
	}

	// Set language if available
	if ( $.timepicker.regional.hasOwnProperty( RWMB_Timepicker.locale ) )
	{
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Timepicker.locale] );
	}
	else if ( $.timepicker.regional.hasOwnProperty( RWMB_Timepicker.localeShort ) )
	{
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Timepicker.localeShort] );
	}

	$( '.rwmb-time' ).each( update );
	$( '.rwmb-input' ).on( 'clone', '.rwmb-time', update );
} );
