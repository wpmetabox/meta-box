jQuery( function( $ )
{
	$( 'body' ).on( 'change', '.rwmb-image-select input', function()
	{
		var $this = $( this ),
			selected = $this.is( ':checked' ),
			$parent = $this.parent();
		if ( selected )
			$parent.addClass( 'rwmb-active' );
		else
			$parent.removeClass( 'rwmb-active' );
	} );
	$( '.rwmb-image-select input' ).trigger( 'change' );
} );