jQuery( document ).ready( function ($) 
{
	// Add more clones
	$( '.add-clone' ).click( function ( event )
	{
		event.preventDefault();

		var 
			$input_last = $( this ).parents( '.rwmb-input' ).find( '.rwmb-clone:last' ),
			$clone      = $input_last.clone( true )
		;

		$clone.insertAfter( $input_last );
	} );

	// Remove clones
	$( '.rwmb-input' ).delegate( '.remove-clone', 'click', function( event )
	{
		event.preventDefault();

		var $this = $( this );

		// Remove clone only if there're 2 or more of them
		if ( $this.parents( '.rwmb-input' ).find( '.rwmb-clone' ).length > 1 )
			$( this ).parent().remove();
	} );
} );