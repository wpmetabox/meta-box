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
			current = $this.val();;

		$this.siblings( '.ui-datepicker-append' ).remove(); // Remove appended text
		if ( $timestamp.length )
		{
			var $picker = $inline.length ? $inline : $this;
			options.onSelect = function ()
			{
				$timestamp.val( Math.floor( getTimestamp( $picker.datepicker( 'getDate' ) ) / 1000 ) );
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
		return Date.UTC( date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds() );
	}

	$( ':input.rwmb-date' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-date', update );
} );
