/**
 * Update select2
 * Used for static & dynamic added elements (when clone)
 */
function rwmb_update_select_advanced()
{
	var $ = jQuery;

	$( ':input.rwmb-select-advanced' ).each( function ()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
		$this.siblings('.select2-container').remove();
		$this.select2( options );
	} );
}

jQuery( document ).ready( function ()
{
	rwmb_update_select_advanced();
} );