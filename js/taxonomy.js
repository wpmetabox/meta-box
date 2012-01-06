// JavaScript Document
jQuery( document ).ready( function($) 
{
	$( "ul.rw-taxonomy-tree li input:checkbox" ).live( "change",
		function() 
		{
			childList = $( this ).siblings( 'ul.rw-taxonomy-tree' );
			if ( $( this ).is( ':checked' ) ) 
			{
				childList.removeClass( "hidden" ).find( 'li' ).each( function()
				{
					if( ! $( this ).parent().hasClass( 'hidden' ) )
					{
						$( this ).children( 'input:checkbox' ).removeAttr( 'disabled' );
					}
				} );
			} 
			else 
			{
				childList.addClass( "hidden" ).find( 'li input:checkbox' ).attr( 'disabled', true );
			}
		}
	);
} );