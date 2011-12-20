jQuery( document ).ready( function($) 
{
	var 
		 id		= null
		,el		= null
		,input	= null
		,label	= null
		,format	= null
		,value	= null
		,update	= null
	;
	$( '.rwmb-slider' ).each( function( i, val ) 
	{
		id		= $( val ).attr( 'id' );
		el		= $( '#' + id );
		input	= $( '[name=' + id + ']' );
		label	= $( '[for=' + id + ']' );
		format	= $( el ).attr( 'rel' );

		$( label ).append( ': <span id="' + id + '-label"></span>' );
		update	= $( '#' + id + '-label' );

		if ( 'undefined' === $( input ).val() || null === typeof $( input ).val() )
		{
			$( input ).val( $( el ).slider( "values", 0 ) );
			$( update ).val( "0" );
		}
		else
		{
			value = $( input ).val();console.log( value );
			$( update ).text( value );
			if ( 0 < format.length )
				$( update ).append( ' ' + format );
		}

		el.slider(
		{
			value:	value,
			slide:	function( event, ui )
			{
				$( input ).val( ui.value );
				$( update ).text( ui.value + ' ' + format );
			}
		} );
		//console.log( $( label + ' span' ) );
	});
} );