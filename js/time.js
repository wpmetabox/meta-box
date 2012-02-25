jQuery( document ).ready( function($) 
{
	$( '.rwmb-time' ).each( function()
	{
		var 
			$this	= $( this ),
			format	= $this.attr( 'rel' )
		;
		var showAmPm = format.match('t') || format.match('T') ? true : false;
		var showSecond = format.match(':s') ? true : false;
		var showMillisec = format.match(':l') ? true : false;
		$this.timepicker(
		{
			showSecond: showSecond,
			showMillisec: showMillisec,
			timeFormat: format,
			ampm: showAmPm;
		} );
	} );
} );