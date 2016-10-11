jQuery( function ( $ ) {
	'use strict';

	/**
	 * Update datetime picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update() {
		var $this = $( this ),
			options = $this.data( 'options' ),
			$inline = $this.siblings( '.rwmb-datetime-inline' ),
			current = $this.val();

		$this.siblings( '.ui-datepicker-append' ).remove();  // Remove appended text

		if ( $inline.length ) {
			options.altField = '#' + $this.attr( 'id' );
			$inline
				.removeClass( 'hasDatepicker' )
				.empty()
				.prop( 'id', '' )
				.timepicker( options )
				.timepicker( "setTime", current );
		}
		else {
			$this.removeClass( 'hasDatepicker' ).timepicker( options );
		}
	}

	// Set language if available
	$.timepicker.setDefaults( $.timepicker.regional[""] );
	if ( $.timepicker.regional.hasOwnProperty( RWMB_Time.locale ) ) {
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Time.locale] );
	}
	else if ( $.timepicker.regional.hasOwnProperty( RWMB_Time.localeShort ) ) {
		$.timepicker.setDefaults( $.timepicker.regional[RWMB_Time.localeShort] );
	}

	$( '.rwmb-time' ).each( update );
	$( '.rwmb-input' ).on( 'clone', '.rwmb-time', update );
} );
