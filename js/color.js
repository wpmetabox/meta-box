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
		if ( ! $input.val() )
			$input.val( '#' );

		$this.farbtastic( $input );
	} );
}

/**
 * Show hide the color picker on field in/focus or out/blur
 * Calls rwmb_update_color_picker() to catch all fields 
 */
jQuery( document ).ready( function($)
{
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

	rwmb_update_color_picker();
} );