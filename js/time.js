jQuery( document ).ready( function($) 
{
	$( '.rwmb-time' ).each( function()
	{
		var 
			$this	= $( this ),
			format	= $this.attr( 'rel' )
		;

		$this.timepicker(
		{
			showSecond: true,
			timeFormat: format
		} );
	} );
} );