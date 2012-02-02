jQuery( document ).ready( function($)
{
	$( '.rw-taxonomy-tree input' ).change( function()
	{
		var $this = $( this ),
			$childList = $this.parent().siblings( '.rw-taxonomy-tree' );
		if ( $this.is( ':checked' ) )
			$childList.removeClass( 'hidden' );
		else
			$childList.addClass( 'hidden' );
	} );
} );