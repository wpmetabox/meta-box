/**
 * Update color picker element
 * Used for static & dynamic added elements (when clone)
 */
function rwmb_update_color_picker()
{
	var $ = jQuery;
	$( '.rwmb-color-picker' ).each( function()
	{
		var $this = $( this ),
			$input = $this.siblings( 'input.rwmb-color' );

		// Make sure the value is displayed
		if ( !$input.val() )
			$input.val( '#' );

		$this.farbtastic( $input );
	} );
}

jQuery( document ).ready( function( $ )
{
	$( '.rwmb-input' ).on( 'focus', '.rwmb-color', function()
	{
		$( this ).siblings( '.rwmb-color-picker' ).show();
		return false;
	} ).on( 'blur',  '.rwmb-color', function()
	{
		$( this ).siblings( '.rwmb-color-picker' ).hide();
		return false;
	} );


	rwmb_update_color_picker();
} );