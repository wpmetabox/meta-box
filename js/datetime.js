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
			options = $this.data( 'options' ),
			$inline = $this.siblings( '.rwmb-datetime-inline' ),
			current = $this.val(),
			id = $this.prop( 'id' );

		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text

		if( $inline.length )
		{
			options.altField = '#' + id;
			$inline
				.removeClass( 'hasDatepicker' )
				.empty()
				.prop( 'id', '' )
				.datetimepicker( options )
				.datetimepicker( "setDate", current );
		}
		else
		{
			$this.removeClass( 'hasDatepicker' ).datetimepicker( options );
		}



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
