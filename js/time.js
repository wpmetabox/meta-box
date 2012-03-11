jQuery( document ).ready( function($) 
{
	$( '.rwmb-time' ).each( function()
	{
		var $this	= $( this ),
			format	= $this.attr( 'rel' ),
			showAmPm = format.match('t') || format.match('T') ? true : false,
			showSecond = format.match(':s') ? true : false,
			showMillisec = format.match(':l') ? true : false;
		$this.timepicker(
		{
			showSecond:   showSecond,
			showMillisec: showMillisec,
			timeFormat:   format,
			ampm:         showAmPm
		} );
	} );
} );