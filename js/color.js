jQuery( document ).ready( function($) 
{
	$( '.rwmb-color-picker' ).each( function() 
	{
		var $this = $( this ), id = $this.attr( 'rel' );

		$this.farbtastic( '#' + id );
	} );

	$( '.rwmb-color-select' ).click( function() 
	{
		$( this ).siblings( '.rwmb-color-picker' ).toggle();
		return false;
	} );
} );