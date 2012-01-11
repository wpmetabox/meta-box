jQuery( document ).ready( function($) 
{
	$( '.rwmb-clone' ).each( function( index, container ) 
	{ 
		var
			 $this			= $( this )
			,id				= $this.attr( 'rel' )
			,fields_all		= $this.find( ':input' ).not( '.rwmb-button' )
			,field_counter	= fields_all.length
			,clone_first	= 1 >= field_counter ? $( fields_all ).first() : $( fields_all ).last()
			,clone_first_id	= $( clone_first ).attr( 'id' ).replace( '[]', '' )
			,clone_last		= $( fields_all ).last()
			,clone_last_id	= $( clone_last ).attr( 'id' ).replace( '[]', '' )
			,field_clone	= 0
			,add_button		= $this.find( '#add_' + id + '_clone'  )
			,add_buttons	= null
			,remove_button	= $this.find( '#remove_' + id + '_clone'  )
			,remove_buttons	= null
			,desc			= $this.find( '#' + id + '_description' )
		;

		// Initial state: Hide remove button if only one field present
		//if ( 1 <= field_counter )
		//	remove_button.hide();

		function clone_setup()
		{
			// hide initial buttons as they're only there to get cloned on demand
			add_button.hide();
			remove_button.hide();

			get_all_fields();
			// Move description
			$( fields_all.last().parent() ).after( desc );

			// Append buttons to each field...
			$.each( fields_all, function( index, field )
			{
				// ...but "remove" not to first one
				if ( index > 0 )
					remove_button.clone().insertAfter( field ).show();

				add_button.clone().insertAfter( field ).show();
			} );

			$( container ).delegate( '.add-clone', 'click', function( event )
			{
				event.preventDefault();

				// Add elements
				current_field	= $( this ).prev( ':input' ).not( ':button' );
				// if we're cloning the first one, let's take the last instead to have both buttons
				if ( current_field[0] === fields_all.first()[0] )
					current_field = fields_all.last();
				current_parent	= current_field.parent();
				field_new		= current_parent.clone().appendTo( current_parent.parent() );

				// Move description
				$( desc ).insertAfter( field_new );

				console.log( field_new );
			} );

			$( container ).delegate( '.remove-clone', 'click', function( event )
			{
				event.preventDefault();

				// Remove elements
				$( this ).parent().remove();

				console.log( $( this ) );
			} );

			/*// Get all "add" buttons
			$.each( get_add_buttons(), function( i, button )
			{
				$( button ).bind( 'click', function( event ) 
				{
					// Add elements
					current_field	= $( button ).prev( ':input' ).not( ':button' ).parent();
					field_new		= current_field.clone().appendTo( current_field.parent() );

					// Move description
					$( desc ).insertAfter( field_new );

					get_add_buttons();
				} );
			} );

			// Get all "remove" buttons
			$.each( get_remove_buttons(), function( i, button )
			{
				$( button ).bind( 'click', function( event ) 
				{
					// Remove elements
					$( button ).parent().remove();

					get_remove_buttons();
				} );
			} );*/
		}

		function get_all_fields()
		{
			fields_all = $this.find( ':input' ).not( '.rwmb-button' );
			return fields_all;
		}

		function get_add_buttons()
		{
			add_buttons = $this.find( '#add_' + id + '_clone'  ).not( ':hidden' );
			return add_buttons;
		}

		function get_remove_buttons()
		{
			remove_buttons = $this.find( '#remove_' + id + '_clone'  ).not( ':hidden' );
			return remove_buttons;
		}

		clone_setup();
/*
		// REMOVE
		remove_button.bind( 'click', function( event )
		{
			// Prevent redirect
			event.preventDefault();

			// Update fields container
			field_counter	= field_counter - 1;

			// We need to update here again
			// Update the new list of clones
			fields_all	= $this.find( ':input' ).not( '.rwmb-button' );
			// Determine the new last clone
			clone_last		= $( fields_all ).last();

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
			fields_all	= $this.find( ':input' ).not( '.rwmb-button' );
			// Determine the new last clone
			clone_last		= $( fields_all ).last();

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
				add_button.insertAfter( clone_last );
				remove_button.insertAfter( clone_last );
			}
		} );
*/
	} );
} );