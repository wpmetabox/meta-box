jQuery( function( $ )
{
	'use strict';

	$( '.rw-taxonomy-tree input:checkbox' ).change( function()
	{
		var $this = $( this ),
			$childList = $this.parent().siblings( '.rw-taxonomy-tree' );
		if ( $this.is( ':checked' ) )
		{
			$childList.removeClass( 'hidden' );
		}
		else
		{
			$childList.find( 'input' ).removeAttr( 'checked' );
			$childList.addClass( 'hidden' );
		}
	} );

	$( '.rw-taxonomy-tree select' ).change( function()
	{
		var $this = $( this ),
			$childList = $this.siblings( '.rw-taxonomy-tree' ),
			$value = $this.val();
		$childList.removeClass( 'active' ).addClass( 'disabled' ).find( 'select' ).each( function()
		{
			$( this ).val( $( 'options:first', this ).val() ).attr( 'disabled', 'disabled' );
		} );
		$childList.filter( '.rwmb-taxonomy-' + $value ).removeClass( 'disabled' ).addClass( 'active' ).children( 'select' ).removeAttr( 'disabled' );
	} );
} );
