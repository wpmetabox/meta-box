jQuery( function ( $ )
{
	'use strict';

	/**
	 * Update date picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update()
	{
		var $this = $( this ),
			options = $this.data( 'options' ),
			$inline = $this.siblings( '.rwmb-datetime-inline' ),
			$timestamp = $this.siblings( '.rwmb-datetime-timestamp' ),
			current = $this.val();

		$this.siblings( '.ui-datepicker-append' ).remove(); // Remove appended text
		if ( $timestamp.length )
		{
			var $picker = $inline.length ? $inline : $this;
			options.onClose = function ()
			{
				$timestamp.val( getTimestamp( $picker.datepicker( 'getDate' ) ) );
			};
		}

		if ( $inline.length )
		{
			options.altField = '#' + $this.attr( 'id' );
			$inline
				.removeClass( 'hasDatepicker' )
				.empty()
				.prop( 'id', '' )
				.datepicker( options )
				.datepicker( 'setDate', current );
		}
		else
		{
			$this.removeClass( 'hasDatepicker' ).datepicker( options );
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

	if ( $.datepicker.regional.hasOwnProperty( RWMB_Date.locale ) )
	{
		$.datepicker.setDefaults( $.datepicker.regional[RWMB_Date.locale] );
	}
	else if ( $.datepicker.regional.hasOwnProperty( RWMB_Date.localeShort ) )
	{
		$.datepicker.setDefaults( $.datepicker.regional[RWMB_Date.localeShort] );
	}

	$( ':input.rwmb-date' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-date', update );
} );
