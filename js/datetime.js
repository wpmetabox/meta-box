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
			format = $this.attr( 'rel' ),
			show_amppm = /t/i.test(format),
			show_second = /:s/.test(format),
			show_millisec = /:l/.test(format);

		$this.removeClass('hasDatepicker').attr('id', '').datetimepicker( {
			showSecond  : show_second,
			showMillisec: show_millisec,
			timeFormat  : format,
			ampm        : show_amppm,
		} );
	} );
}

jQuery( document ).ready( function($)
{
	rwmb_update_datetime_picker();
} );