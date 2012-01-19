jQuery( document ).ready( function($) 
{
	$( '.rwmb-datetime' ).on( 
		 'focusin'
		,function( handler )
		{
			var 
				$this = $( this ),
				format = $this.attr( 'rel' )
			;

			$this.datetimepicker(
			{
				showSecond:	true,
				timeFormat:	format
			} );
		}
	);
} );