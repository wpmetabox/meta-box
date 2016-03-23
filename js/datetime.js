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
			hasInline = $inline.length > 0,
			$timestamp = $this.siblings( '.rwmb-datetime-timestamp' ),
			current = $this.val(),
			id = $this.prop( 'id' );

		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text
		if( $timestamp.length )
		{
			var $pickerElement = hasInline ? $inline : $this;
			options.onSelect = function( date, inst )
			{
				$timestamp.val( Math.floor( createDateAsUTC( $pickerElement.datetimepicker( 'getDate' ) ) / 1000) );
			};
		}

		if( hasInline )
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
	//UTC functions.  See http://stackoverflow.com/a/14006555/556258
	function createDateAsUTC(date) {
		return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds()));
	}

	function convertDateToUTC(date) {
		return new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds());
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
