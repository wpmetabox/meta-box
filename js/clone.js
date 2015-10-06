/* global jQuery, rwmb_cloneable_editors */

jQuery( function ( $ )
{
	'use strict';

	// Object holds all methods related to fields' index when clone
	var cloneIndex = {
		/**
		 * Reset index for fields in .rwmb-clone
		 * Must be done when add/remove or sort clone
		 * @param $container A div container which has all fields
		 */
		reset      : function ( $container )
		{
			var index = 0;
			$container.find( '.rwmb-clone' ).each( function ()
			{
				cloneIndex.set( $( this ), index++ );
			} );
		},
		/**
		 * Set index for fields in a .rwmb-clone
		 * @param $clone .rwmb-clone element
		 * @param index Index value
		 */
		set        : function ( $clone, index )
		{
			$clone.find( ':input[class|="rwmb"]' ).each( function ()
			{
				var $field = $( this );

				// Name attribute
				var name = $field.attr( 'name' );
				if ( name )
				{
					$field.attr( 'name', cloneIndex.replace( index, name, '[', ']', false ) );
				}

				// ID attribute
				var id = $field.attr( 'id' );
				if ( id )
				{
					$field.attr( 'id', cloneIndex.replace( index, id, '_' ) );
				}
			} );

			// Address button's value attribute
			var $address = $clone.find( '.rwmb-map-goto-address-button' );
			if ( $address.length )
			{
				var value = $address.attr( 'value' );
				$address.attr( 'value', cloneIndex.replace( index, value, '_' ) );
			}
		},
		/**
		 * Replace an attribute of a field with updated index
		 * @param index New index value
		 * @param value Attribute value
		 * @param before String before returned value
		 * @param after String after returned value
		 * @param alternative Check if attribute does not contain any integer, will reset the attribute?
		 * @return string
		 */
		replace    : function ( index, value, before, after, alternative )
		{
			before = before || '';
			after = after || '';
			alternative = alternative || true;

			var regex = new RegExp( cloneIndex.escapeRegex( before ) + '(\\d+)' + cloneIndex.escapeRegex( after ) ),
				newValue = before + index + after;

			return regex.test( value ) ? value.replace( regex, newValue ) : (alternative ? value + newValue : value );
		},
		/**
		 * Helper function to escape string in regular expression
		 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions
		 * @param string
		 * @return string
		 */
		escapeRegex: function ( string )
		{
			return string.replace( /[.*+?^${}()|[\]\\]/g, "\\$&" );
		}
	};

	/**
	 * Clone fields
	 * @param $container A div container which has all fields
	 * @return void
	 */
	function clone( $container )
	{
		var $last = $container.children( '.rwmb-clone:last' ),
			$clone = $last.clone(),
			$input = $clone.find( ':input[class|="rwmb"]' );

		$input.each( function ()
		{
			var $field = $( this );
			if ( $field.attr( 'type' ) === 'radio' || $field.attr( 'type' ) === 'checkbox' )
			{
				// Reset 'checked' attribute
				$field.prop( 'checked', false );
			}
			else
			{
				// Reset value
				$field.val( '' );
			}
		} );

		$clone.insertAfter( $last );
		cloneIndex.reset( $container );

		// Toggle remove buttons
		toggleRemoveButtons( $input );

		// Trigger custom clone event
		$input.trigger( 'clone' );
	}

	/**
	 * Hide remove buttons when there's only 1 of them
	 *
	 * @param $container .rwmb-input container
	 *
	 * @return void
	 */
	function toggleRemoveButtons( $container )
	{
		var $button = $container.find( '.remove-clone' );
		$button[$button.length < 2 ? 'hide' : 'show']();
	}

	/**
	 * Toggle add button
	 * Used with [data-max-clone] attribute. When max clone is reached, the add button is hid and vice versa
	 *
	 * @param $container .rwmb-input container
	 *
	 * @return void
	 */
	function toggleAddButton( $container )
	{
		var $button = $container.find( '.add-clone' ),
			maxClone = parseInt( $container.data( 'max-clone' ) ),
			numClone = $container.find( '.rwmb-clone' ).length;

		$button[isNaN( maxClone ) || (maxClone && numClone < maxClone) ? 'show' : 'hide']();
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
			field_id = field_name.replace( /\[(\d+)]/, '' );

		//Create some global vars
		var new_index = 0;
		var new_name = field_name.replace( /\[(\d+)]/, function ( match, p1 )
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
			var new_id = cloned_editor.attr( 'id' ).replace( /\[(\d+)]/, '[' + new_index + ']' );
			cloned_editor.attr( 'id', new_id );

			//Replace all IDs within cloned field
			cloned_editor.find( '[id*="' + field_id + '"]' ).each( function ()
			{
				var id = $( this ).attr( 'id' ).replace( /\[(\d+)]/, '[' + new_index + ']' );
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

	$( '#poststuff' )
		// Add clones
		.on( 'click', '.add-clone', function ( e )
		{
			e.preventDefault();

			var $container = $( this ).closest( '.rwmb-input' );

			cloneIndex.reset( $container );

			if ( $( this ).closest( '.rwmb-field' ).hasClass( 'rwmb-wysiwyg-wrapper' ) )
			{
				cloneWYSIWYG( $container );
			}
			else
			{
				clone( $container );
			}

			toggleRemoveButtons( $container );
			toggleAddButton( $container );
		} )
		// Remove clones
		.on( 'click', '.remove-clone', function ( e )
		{
			e.preventDefault();

			var $this = $( this ),
				$container = $this.closest( '.rwmb-input' );

			// Remove clone only if there are 2 or more of them
			if ( $container.find( '.rwmb-clone' ).length < 2 )
			{
				return;
			}

			$this.parent().trigger( 'remove' ).remove();
			cloneIndex.reset( $container );
			toggleRemoveButtons( $container );
			toggleAddButton( $container )
		} );

	$( '.rwmb-input' ).each( function ()
	{
		var $container = $( this );
		cloneIndex.reset( $container );
		toggleRemoveButtons( $container );
		toggleAddButton( $container );

		$container.sortable( {
			handle     : '.rwmb-clone-icon',
			placeholder: ' rwmb-clone rwmb-clone-placeholder',
			items      : '.rwmb-clone',
			start      : function ( event, ui )
			{
				// Make the placeholder has the same height as dragged item
				ui.placeholder.height( ui.item.height() );
			},
			update     : function ()
			{
				cloneIndex.reset( $container );
			}
		} );
	} );
} );
