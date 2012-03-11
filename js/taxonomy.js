jQuery( document ).ready( function($)
{
	$( '.rw-taxonomy-tree input:checkbox' ).change( function()
	{
		var $this = $( this ),
			$childList = $this.parent().siblings( '.rw-taxonomy-tree' );
		if ( $this.is( ':checked' ) )
			$childList.removeClass( 'hidden' );
		else
		{
			$('input', $childList).removeAttr('checked');
			$childList.addClass( 'hidden' );
		}
	} );
} );