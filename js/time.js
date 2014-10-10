jQuery( function ( $ )
{
	'use strict';

	/**
	 * Update datetime picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function rwmb_update_time_picker()
	{
		var $this = $( this ),
			options = $this.data( 'options' );

		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text
		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).timepicker( options );
	}

	$( ':input.rwmb-time' ).each( rwmb_update_time_picker );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-time', rwmb_update_time_picker );
} );
