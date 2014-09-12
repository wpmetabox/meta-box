jQuery( function( $ )
{
	'use strict';

	$( document ).ajaxSend( function( e, xhr, s )
	{
		if ( typeof s.data !== 'undefined' &&  -1 !== s.data.indexOf( 'action=autosave' ) )
		{
			$( '.rwmb-meta-box').each( function()
			{
				var $meta_box = $( this );
				if ( $meta_box.data( 'autosave' ) === true )
				{
					s.data += '&' + $meta_box.find( ':input' ).serialize();
				}
			} );
		}
	} );
} );
