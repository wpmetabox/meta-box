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
			$timestamp = $this.siblings( '.rwmb-datetime-timestamp' ),
			current = $this.val(),
			$picker = $inline.length ? $inline : $this;

		$this.siblings( '.ui-datepicker-append' ).remove(); // Remove appended text
		if ( $timestamp.length )
		{
			options.onClose = options.onSelect = function ()
			{
				$timestamp.val( getTimestamp( $picker.datetimepicker( 'getDate' ) ) );
			};
		}

		if ( $inline.length )
		{
			options.altField = '#' + $this.attr( 'id' );
			$this.on( 'keydown', _.debounce( function(){
				$picker
					.datepicker( 'setDate', $this.val() )
					.find(".ui-datepicker-current-day")
					.trigger("click");
			}, 600 ) );
			
			$inline
				.removeClass( 'hasDatepicker' )
				.empty()
				.prop( 'id', '' )
				.datetimepicker( options )
				.datetimepicker( 'setDate', current );
		}
		else
		{
			$this.removeClass( 'hasDatepicker' ).datetimepicker( options );
		}
	}

	/**
	 * Convert date to Unix timestamp in milliseconds
	 * @link http://stackoverflow.com/a/14006555/556258
	 * @param date
	 * @return number
	 */
	function getTimestamp( date )
	{
		if ( date === null )
			return "";
		var milliseconds = Date.UTC( date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds() );
		return Math.floor( milliseconds / 1000 );
	}

	// Set language if available
	$.datepicker.setDefaults( $.datepicker.regional[ "" ] );
	if ( $.datepicker.regional.hasOwnProperty( RWMB_Datetime.locale ) )
	{
		$.datepicker.setDefaults( $.datepicker.regional[RWMB_Datetime.locale] );
	}
	else if ( $.datepicker.regional.hasOwnProperty( RWMB_Datetime.localeShort ) )
	{
		$.datepicker.setDefaults( $.datepicker.regional[RWMB_Datetime.localeShort] );
	}
	$.timepicker.setDefaults( $.timepicker.regional[ "" ] );
	if ( $.timepicker.regional.hasOwnProperty( RWMB_Datetime.locale ) )
	{
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Datetime.locale] );
	}
	else if ( $.timepicker.regional.hasOwnProperty( RWMB_Datetime.localeShort ) )
	{
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Datetime.localeShort] );
	}

	$( ':input.rwmb-datetime' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-datetime', update );
} );
