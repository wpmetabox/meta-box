/**
 * Update date picker element
 * Used for static & dynamic added elements (when clone)
 */
function rwmb_update_date_picker()
{
	var $ = jQuery;

	$( '.rwmb-date' ).each( function()
	{
		var $this = $( this ),
			options = $this.data( 'options' );

		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).datepicker( options );
	} );
}

jQuery( document ).ready( function($)
{
	rwmb_update_date_picker();
} );