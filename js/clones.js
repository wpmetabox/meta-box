jQuery( document ).ready( function($) 
{
	$( '.rwmb-clone' ).each( function() 
	{ 
		var
			 $this			= $( this )
			,container_id	= $this.attr( 'rel' )
			,clone_fields	= $this.find( ':input' ).not( '.rwmb-button' )
			,field_counter	= clone_fields.length
			,clone_first	= 1 >= field_counter ? $( clone_fields ).first() : $( clone_fields ).last()
			,clone_first_id	= $( clone_first ).attr( 'id' ).replace( '[]', '' )
			,clone_last		= $( clone_fields ).last()
			,clone_last_id	= $( clone_last ).attr( 'id' ).replace( '[]', '' )
			,field_clone	= 0
			,add_button		= $this.find( '#add_' + clone_first_id + '_clone'  )
			,remove_button	= $this.find( '#remove_' + clone_first_id + '_clone'  )
			,desc			= $this.find( '#' + clone_first_id + '_description' )
		;

		// Initial state: Hide remove button if only one field present
		if ( 1 <= field_counter )
			remove_button.hide();

		// REMOVE
		remove_button.bind( 'click', function( event )
		{
			// Prevent redirect
			event.preventDefault();

			// Update fields container
			field_counter	= field_counter - 1;

			// We need to update here again
			// Update the new list of clones
			clone_fields	= $this.find( ':input' ).not( '.rwmb-button' );
			// Determine the new last clone
			clone_last		= $( clone_fields ).last();

			// Only delete fields as long as we got more than one field
			if ( 0 < field_counter )
			{
				$( clone_last ).remove();
				$this.find( ':not(.description) > .clear' ).last().remove();
			}
			// Move buttons
			if ( 1 < field_counter )
			{
				add_button.insertAfter( clone_last );
				remove_button.insertAfter( clone_last );
			}
			else
			{
				add_button.insertAfter( clone_first );
				remove_button.hide();
			}
		} );

		// ADD
		add_button.bind( 'click', function( event )
		{
			// Prevent redirect
			event.preventDefault();

			// Update fields container
			field_counter	= field_counter + 1;

			// Clone the field
			field_clone		= $( clone_first ).clone();
			// Update the new list of clones
			clone_fields	= $this.find( ':input' ).not( '.rwmb-button' );

			// Add the counter nr. to the clone id
			field_clone.attr( 'id', clone_first_id + "_" + field_counter + "[]" );
			// Move Clone
			field_clone.appendTo( $this.find( '.rwmb-input' ).last() );
			// Add clear break for next Clone
			remove_button.before( '<br class="clear" />' );

			// Move buttons
			if ( 1 < field_counter )
			{
				add_button.insertAfter( field_clone );
				remove_button.show();
				remove_button.insertAfter( field_clone );
				// Move the description
				desc.remove().insertAfter( add_button );
			}
			else
			{
				add_button.insertAfter( clone_fields.last() );
				remove_button.insertAfter( clone_fields.last() );
			}
		} );
	} );
} );