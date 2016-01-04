jQuery( function( $ )
{
	'use strict';
	function updateChecklist()
	{
		var $this = $( this ),
			$children = $this.closest( 'li' ).children('ul');	
			
		if ( $this.is( ':checked' ) )
		{
			$children.removeClass( 'hidden' );
		}
		else
		{
			$children
				.addClass( 'hidden' )
				.find( 'input' )
				.removeAttr( 'checked' );
		}
	}
	
	$( '.rwmb-checklist :checkbox' )
		.each( updateChecklist )
		.change( updateChecklist );	
} );
