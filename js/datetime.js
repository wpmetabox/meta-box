/**
 * Update datetime picker element
 * Used for static & dynamic added elements (when clone)
 */
function rwmb_update_datetime_picker()
{
	var $ = jQuery;

	$( '.rwmb-datetime' ).each( function()
	{
		var $this = $( this ),
			options = $this.data( 'options' );

		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text
		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).datetimepicker( options );
	} );
}

jQuery( function( $ )
{
	$.datepicker.setDefaults( $.datepicker.regional[RWMB_Datetimepicker.lang] );
	$.timepicker.setDefaults( $.timepicker.regional[RWMB_Datetimepicker.lang] );
	rwmb_update_datetime_picker();
} );
