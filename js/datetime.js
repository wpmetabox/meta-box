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

		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text
		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).datetimepicker( options );

	}

	// Set language if available
	if ( $.timepicker.regional.hasOwnProperty( RWMB_Datetimepicker.locale ) )
	{
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Datetimepicker.locale] );
	}
	else if ( $.timepicker.regional.hasOwnProperty( RWMB_Datetimepicker.localeShort ) )
	{
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Datetimepicker.localeShort] );
	}

	$( ':input.rwmb-datetime' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-datetime', update );
} );
