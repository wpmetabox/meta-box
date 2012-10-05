/**
 * Update datetime picker element
 * Used for static & dynamic added elements (when clone)
 */
function rwmb_update_time_picker()
{
	var $ = jQuery;

	$( '.rwmb-time' ).each( function()
	{
		var $this = $( this ),
			options = $this.data( 'options' );

		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).datetimepicker( options );
	} );
}

jQuery( document ).ready( function($)
{
	rwmb_update_time_picker();
} );