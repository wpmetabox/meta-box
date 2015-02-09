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
	var locale = RWMB_Timepicker.lang;
	if ( $.timepicker.regional.hasOwnProperty( locale ) )
	{
		$.timepicker.setDefaults( $.timepicker.regional[locale] );
	}

	$( '.rwmb-time' ).each( update );
	$( '.rwmb-input' ).on( 'clone', '.rwmb-time', update );
} );
