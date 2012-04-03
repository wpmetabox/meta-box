jQuery( document ).ready( function($)
{
	$( '.rwmb-color-picker' ).each( function()
	{
		var $this = $( this ), 
			id    = $this.attr( 'rel' );

		$this.farbtastic( '#' + id );
	} );

	$( '.rwmb-color' )
	// On field in/focus
	.focus( function()
	{
		$( this ).siblings( '.rwmb-color-picker' ).show();
		return false;
	} )
	// On field out/blur
	.blur( function() 
	{
		$( this ).siblings( '.rwmb-color-picker' ).hide();
		return false;
	} );
} );