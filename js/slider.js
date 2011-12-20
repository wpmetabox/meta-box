jQuery( document ).ready( function($) 
{
	var 
		 id		= null
		,el		= null
		,input	= null
		,value	= null
	;
	$( '.rwmb-slider' ).each( function( i, val ) 
	{
		id		= jQuery( val ).attr( 'id' );
		el		= jQuery( '#' + id );
		input	= jQuery( '[name=' + id + ']' );

		if ( 'undefined' === $( input ).val() || null === typeof $( input ).val() )
		{
			$( input ).val( $( el ).slider( "values", 0 ) );
		}
		else
		{
			value = $( input ).val();
		}

		el.slider(
		{
			value:	value,
			slide:	function( event, ui )
			{
				$( input ).val( ui.value );
			}
		} );
	});
} );