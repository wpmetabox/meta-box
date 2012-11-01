/**
 * Update select2
 * Used for static & dynamic added elements (when clone)
 */
function rwmb_update_select_advanced()
{
	var $ = jQuery;

	$( '.rwmb-select-advanced' ).each( function ()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
		$this.select2( options );
	} );
}

jQuery( document ).ready( function ()
{
	rwmb_update_select_advanced();
} );