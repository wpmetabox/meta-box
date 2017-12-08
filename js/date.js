jQuery( function ( $ ) {
	'use strict';

	/**
	 * Update date picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update() {
		var $this = $( this ),
			options = $this.data( 'options' ),
			$inline = $this.siblings( '.rwmb-datetime-inline' ),
			$timestamp = $this.siblings( '.rwmb-datetime-timestamp' ),
			current = $this.val(),
			$picker = $inline.length ? $inline : $this;

		$this.siblings( '.ui-datepicker-append' ).remove(); // Remove appended text
		if ( $timestamp.length ) {
			options.onClose = options.onSelect = function () {
				$timestamp.val( getTimestamp( $picker.datepicker( 'getDate' ) ) );
			};
		}

		if ( $inline.length ) {
			options.altField = '#' + $this.attr( 'id' );
			$this.on( 'keydown', _.debounce( function () {
				// if val is empty, return to allow empty datepicker input.
				if ( !$this.val() ) {
					return;
				}
				$picker
					.datepicker( 'setDate', $this.val() )
					.find( ".ui-datepicker-current-day" )
					.trigger( "click" );
			}, 600 ) );

			$inline
				.removeClass( 'hasDatepicker' )
				.empty()
				.prop( 'id', '' )
				.datepicker( options )
				.datepicker( 'setDate', current );
		}
		else {
			$this.removeClass( 'hasDatepicker' ).datepicker( options );
		}
	}

	/**
	 * Convert date to Unix timestamp in milliseconds
	 * @link http://stackoverflow.com/a/14006555/556258
	 * @param date
	 * @return number
	 */
	function getTimestamp( date ) {
		if ( date === null ) {
			return "";
		}
		var milliseconds = Date.UTC( date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds() );
		return Math.floor( milliseconds / 1000 );
	}

	$( '.rwmb-date' ).each( update );
	$( document ).on( 'clone', '.rwmb-date', update );
} );
