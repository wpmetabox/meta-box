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
				.datepicker( options )
				.datepicker( "setDate", current );
		}
		else
		{
			$this.removeClass( 'hasDatepicker' ).datepicker( options );
		}
	}

	$( ':input.rwmb-date' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-date', update );
} );
