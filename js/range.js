jQuery( function ( $ )
{
	'use strict';

	/**
	 * Update color picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update()
	{
		var $this = $( this ),
			$output = $this.siblings( '.rwmb-output' );

    $this.on( 'input propertychange change', function( e )
    {
      $output.html( $this.val() );
    } );

	}

	$( ':input.rwmb-range' ).each( update );
	$( '.rwmb-input' ).on( 'clone', 'input.rwmb-range', update );
} );
