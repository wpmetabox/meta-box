/**
 * Update date picker element
 * Used for static & dynamic added elements (when clone)
 */
function rwmb_update_date_picker()
{
	var $ = jQuery;

	$( '.rwmb-date' ).each( function()
	{
		var $this = $( this ),
			picker,
			format = $this.attr( 'rel' ),
			custom = $this.attr( 'data-options' );

		picker = $this.removeClass('hasDatepicker').attr('id', '').datepicker( {
			showButtonPanel: true,
			dateFormat:	     format
		} );

		if ( typeof(custom) != 'undefined' ) {
			custom = JSON.parse(custom);  
				for (var key in custom) {
					picker.datepicker( "option" , key , custom[key] ); 
			}
		}

	} );
}

jQuery( document ).ready( function($)
{
	rwmb_update_date_picker();
} );