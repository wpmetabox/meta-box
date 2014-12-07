jQuery( function ( $ )
{
	'use strict';

	/**
	 * Clone fields
	 * @param $container A div container which has all fields
	 * @return void
	 */
	function clone( $container )
	{
		var $clone_last = $container.find( '.rwmb-clone:last' ),
			$clone = $clone_last.clone(),
			$input;

		$clone.insertAfter( $clone_last );
		$input = $clone.find( ':input[class|="rwmb"]' );

		// Increment each field type
		$input.each( function ()
		{
			var $this = $( this );

			if ( $this.attr( 'type' ) === 'radio' || $this.attr( 'type' ) === 'checkbox' )
			{
				// Reset 'checked' attribute
				$this.removeAttr( 'checked' );
			}
			else
			{
				// Reset value
				$this.val( '' );
			}

			// Get the field name, and increment
			// Not all fields require id, such as 'autocomplete'
			var name = $this.attr( 'name' );
			if ( name )
			{
				name = name.replace( /\[(\d+)\]/, function ( match, p1 )
				{
					return '[' + ( parseInt( p1, 10 ) + 1 ) + ']';
				} );

				// Update the "name" attribute
				$this.attr( 'name', name );
			}

			// Get the field id, and increment
			// Not all fields require id, such as 'radio', 'checkbox_list'
			var id = $this.attr( 'id' );
			if ( id )
			{
				if ( /_(\d+)/.test( id ) )
				{
					id = id.replace( /_(\d+)/, function ( match, p1 )
					{
						return '_' + ( parseInt( p1, 10 ) + 1 );
					} );
				}
				else
				{
					id += '_1';
				}

				// Update the "id" attribute
				$this.attr( 'id', id );
			}

			// Update the address_button "value" attribute
			var $address_button = $clone.find( '.rwmb-map-goto-address-button' );
			if ( $address_button )
			{
				var value = $address_button.attr( 'value' );
				if ( /_(\d+)/.test( value ) )
				{
					value = value.replace( /_(\d+)/, function ( match, p1 )
					{
						return '_' + ( parseInt( p1, 10 ) + 1 );
					} );
				}
				else
				{
					value += '_1';
				}
				$address_button.attr( 'value', value );
			}
		} );

		// Toggle remove buttons
		toggleRemoveButtons( $input );

		// Trigger custom clone event
		$input.trigger( 'clone' );
	}

	/**
	 * Hide remove buttons when there's only 1 of them
	 *
	 * @param $el jQuery element. If not supplied, the function will applies for all fields
	 *
	 * @return void
	 */
	function toggleRemoveButtons( $el )
	{
		var $button;
		$el = $el || $( '.rwmb-field' );
		$el.each( function ()
		{
			$button = $( this ).find( '.remove-clone' );
			if ( $button.length < 2 )
			{
				$button.hide();
			}
			else
			{
				$button.show();
			}
		} );
	}

	/**
	 * Clone WYSIWYG field
	 * @param $container
	 * @return void
	 */
	function cloneWYSIWYG( $container )
	{
		var $clone_first = $container.find( '.rwmb-clone:first' ),
			$clone_last = $container.find( '.rwmb-clone:last' ),
			$clone = $( '<div />' ).addClass( 'rwmb-clone' ),
			field_name = $clone_last.find( 'textarea.wp-editor-area' ).attr( 'name' ),
			field_id = field_name.replace( /\[(\d+)\]/, '' );

		//Create some global vars
		var new_index = 0;
		var new_name = field_name.replace( /\[(\d+)\]/, function ( match, p1 )
		{
			new_index = ( parseInt( p1, 10 ) + 1 );
			return '[' + new_index + ']';
		} );

		if ( typeof rwmb_cloneable_editors !== 'undefined' && typeof rwmb_cloneable_editors[field_id] !== 'undefined' )
		{
			//Get HTML of editor from global object
			var cloned_editor = $( rwmb_cloneable_editors[field_id] );

			//Fill new clone with html form global object
			$clone.append( cloned_editor );

			//Add remove button to clone
			$clone.append( $clone_last.find( '.remove-clone' ).clone() );

			//Add new clone after the last clone
			$clone.insertAfter( $clone_last );

			//Replace ID of field with new ID
			var new_id = cloned_editor.attr( 'id' ).replace( /\[(\d+)\]/, '[' + new_index + ']' );
			cloned_editor.attr( 'id', new_id );

			//Replace all IDs within cloned field
			cloned_editor.find( '[id*="' + field_id + '"]' ).each( function ()
			{
				var id = $( this ).attr( 'id' ).replace( /\[(\d+)\]/, '[' + new_index + ']' );
				$( this ).attr( 'id', id );
			} );

			//Get the new textarea element
			var textarea = $( cloned_editor ).find( 'textarea.wp-editor-area' );

			// Update the "name" attribute
			textarea.attr( 'name', new_name );

			//Empty the textarea
			textarea.html( '' );

			//Update editor link, so we can add media to the new editor
			cloned_editor.find( '#insert-media-button' ).data( 'editor', new_name );


			//Get TinyMCE setting for our fields
			var tmceinit = tinyMCEPreInit.mceInit[$clone_first.find( 'textarea.wp-editor-area' ).attr( 'name' )];
			var tmceqtinit = tinyMCEPreInit.qtInit[$clone_first.find( 'textarea.wp-editor-area' ).attr( 'name' )];

			//Replace id & elements with new created field names
			tmceinit.elements = new_name;
			tmceqtinit.id = new_name;

			//Initialize TinyMCE
			try
			{
				tinymce.init( tmceinit );
			}
			catch ( e )
			{
			}
			if ( typeof(QTags) === 'function' )
			{
				try
				{
					quicktags( tmceqtinit );
				}
				catch ( e )
				{
				}
			}

			// Toggle remove buttons
			toggleRemoveButtons( $clone );

			//Trigger custom clone event
			textarea.trigger( 'clone' );
		}

	}


	// Add more clones
	$( '.add-clone' ).on( 'click', function ( e )
	{
		e.preventDefault();

		var $input = $( this ).parents( '.rwmb-input' );

		if ( $( this ).parents( '.rwmb-field' ).hasClass( 'rwmb-wysiwyg-wrapper' ) )
		{
			cloneWYSIWYG( $input );
		}
		else
		{
			clone( $input );
		}

		toggleRemoveButtons( $input );
	} );

	// Remove clones
	$( '.rwmb-input' ).on( 'click', '.remove-clone', function ( e )
	{
		e.preventDefault();

		var $this = $( this ),
			$input = $this.parents( '.rwmb-input' );

		// Remove clone only if there're 2 or more of them
		if ( $input.find( '.rwmb-clone' ).length <= 1 )
		{
			return;
		}

		$this.parent().remove();

		// Toggle remove buttons
		toggleRemoveButtons( $input );
	} );

	toggleRemoveButtons();
} );
