jQuery( document ).ready( function($)
{
	$( '.rwmb-color-picker' ).each( function()
	{
		var $this = $( this ), id = $this.attr( 'rel' );

		$this.farbtastic( '#' + id );
	} );

	$( '.rwmb-color' ).focus( function()
	{
		$( this ).siblings( '.rwmb-color-picker' ).show();
		return false;
	} ).blur( function() {
		$( this ).siblings( '.rwmb-color-picker' ).hide();
		return false;
	} );
} );