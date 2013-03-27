/**
 * Update color picker element
 * Used for static & dynamic added elements (when clone)
 */
jQuery( document ).ready( function( $ )
{
	
	$( ':input.rwmb-color' ).each( rwmb_update_color_picker );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-color', rwmb_update_color_picker )
	.on( 'focus', '.rwmb-color', function()
	{
		$( this ).siblings( '.rwmb-color-picker' ).show();
		return false;
	} ).on( 'blur',  '.rwmb-color', function()
	{
		$( this ).siblings( '.rwmb-color-picker' ).hide();
		return false;
	} );
	
	function rwmb_update_color_picker()
	{
		var $this = $( this ),
			$color_picker = $this.siblings( '.rwmb-color-picker' );
	
		// Make sure the value is displayed
		if ( !$this.val() )
			$this.val( '#' );
	
		$color_picker.farbtastic( $this );
		
	}

} );