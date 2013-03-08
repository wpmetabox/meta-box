jQuery( function( $ )
{
	$( document ).ajaxSend( function( e, xhr, s )
	{
		if ( -1 != s.data.indexOf( 'action=autosave' ) )
		{
			$( '.rwmb-meta-box :input' ).each( function()
			{
				s.data += '&' + $( this ).serialize();
			} );
		}
	} );
} );
