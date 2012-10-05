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
			picker,
			format = $this.attr( 'rel' ),
			custom = $this.attr( 'data-options' ),
			show_amppm = /t/i.test(format),
			show_second = /:s/.test(format),
			show_millisec = /:l/.test(format);

		picker = $this.removeClass('hasDatepicker').attr('id', '').datetimepicker( {
			showSecond  : show_second,
			showMillisec: show_millisec,
			timeFormat  : format,
			ampm        : show_amppm,
		} );

		if ( typeof(custom) != 'undefined' ) {
			custom = JSON.parse(custom);  
				for (var key in custom) {
					picker.datepicker( "option" , key , custom[key] ); 
			}
		}

	} );
}

jQuery( document ).ready( function($)
{
	rwmb_update_datetime_picker();
} );