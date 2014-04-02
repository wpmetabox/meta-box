jQuery( document ).ready( function( $ )
{
	toggle_remove_buttons();

	function add_cloned_fields( $input )
	{
		var $clone_last = $input.find( '.rwmb-clone:last' ),
			$clone = $clone_last.clone(),
			$input, $address_button, name, id;
	
		$clone.insertAfter( $clone_last );
		$input = $clone.find( ':input[class|="rwmb"]' );
		$address_button = $clone.find( '.rwmb-map-goto-address-button' );

		// Reset value
		$input.val( '' );

		// Get the field name, and increment
		name = $input.attr( 'name' ).replace( /\[(\d+)\]/, function( match, p1 )
		{
			return '[' + ( parseInt( p1 ) + 1 ) + ']';
		} );

		// Update the "name" attribute
		$input.attr( 'name', name );

		// Get the field id, and increment
		var pattern = new RegExp(/_(\d+)/);
		if (pattern.test($input.attr( 'id' ))) {
			id = $input.attr( 'id' ).replace( /_(\d+)/, function( match, p1 )
			{
				return '_' + ( parseInt( p1 ) + 1 ) ;
			} );
		} else {
			id = $input.attr( 'id' ) + '_1';
		}

		// Update the "id" attribute
		$input.attr( 'id', id );

		// Update the address_button "value" attribute
		if($address_button) {
			if (pattern.test($address_button.attr( 'value' ))) {
				id = $address_button.attr( 'value' ).replace( /_(\d+)/, function( match, p1 )
				{
					return '_' + ( parseInt( p1 ) + 1 ) ;
				} );
			} else {
				id = $address_button.attr( 'value' ) + '_1';
			}
			$address_button.attr( 'value', id );
		}

		// Toggle remove buttons
		toggle_remove_buttons( $input );
		
		//Trigger custom clone event
		$input.trigger( 'clone' );
	}

	function add_wysiwyg_clone( $input )
	{
		var	$clone_first = $input.find( '.rwmb-clone:first'),
				$clone_last = $input.find( '.rwmb-clone:last'),
				$clone = $( '<div />' ).addClass('rwmb-clone'),
				field_name = $clone_last.find('textarea.wp-editor-area').attr( 'name' ),
				field_id = field_name.replace( /\[(\d+)\]/, '');


		//Create some global vars
		var new_index = 0;
		var new_name =  field_name.replace( /\[(\d+)\]/, function( match, p1 )
		{
			new_index = ( parseInt( p1 ) + 1 );
			return '[' + new_index + ']';
		} );

		if( typeof rwmb_cloneable_editors !== 'undefined' && typeof rwmb_cloneable_editors[field_id] !== 'undefined') {

			//Get HTML of editor from global object
			var cloned_editor = $(rwmb_cloneable_editors[field_id]);

			//Fill new clone with html form global object
			$clone.append( cloned_editor );

			//Add remove button to clone
			$clone.append($clone_last.find('.remove-clone').clone() );

			//Add new clone after the last clone
			$clone.insertAfter( $clone_last );

			//Replace ID of field with new ID
			var new_id = cloned_editor.attr( 'id' ).replace( /\[(\d+)\]/, '['+new_index+']' );
			cloned_editor.attr( 'id', new_id );

			//Replace all IDs within cloned field
			cloned_editor.find( '[id*="'+field_id+'"]' ).each( function(){
				var id = $(this).attr('id').replace( /\[(\d+)\]/, '['+new_index+']' );
				$(this).attr( 'id', id );
			} );

			//Get the new textarea element
			var textarea = $(cloned_editor).find( 'textarea.wp-editor-area' );

			// Update the "name" attribute
			textarea.attr( 'name', new_name );

			//Empty the textarea
			textarea.html( '' );

			//Update editor link, so we can add media to the new editor
			cloned_editor.find( '#insert-media-button').data( 'editor', new_name );


			//Get TinyMCE setting for our fields
			var tmceinit = tinyMCEPreInit.mceInit[  $clone_first.find('textarea.wp-editor-area').attr( 'name' ) ];
			var tmceqtinit = tinyMCEPreInit.qtInit[ $clone_first.find('textarea.wp-editor-area').attr( 'name' ) ];

			//Replace id & elements with new created field names
			tmceinit.elements = new_name;
			tmceqtinit.id = new_name;

			//Initialize TinyMCE
			try { tinymce.init( tmceinit ); } catch(e){}
			if ( typeof(QTags) == 'function' ) {
				try { quicktags( tmceqtinit ); } catch(e){}
			}

			// Toggle remove buttons
			toggle_remove_buttons( $clone );

			//Trigger custom clone event
			textarea.trigger( 'clone' );
		}

	}

	// Add more clones
	$( '.add-clone' ).on( 'click', function( e )
	{
		e.stopPropagation();
		var $input = $( this ).parents( '.rwmb-input' ),
			$clone_group = $( this ).parents( '.rwmb-field' ).attr( "clone-group" );

		// If the field is part of a clone group, get all fields in that
		// group and itterate over them
		if ( $clone_group )
		{
			// Get the parent metabox and then find the matching
			// clone-group elements inside
			var $metabox = $( this ).parents( '.inside' );
			var $clone_group_list = $metabox.find( 'div[clone-group="' + $clone_group + '"]' );

			$.each( $clone_group_list.find( '.rwmb-input' ),
				function( key, value )
				{
					add_cloned_fields( $( value ) );
				} );
		}
		else if ( $( this).parents( '.rwmb-field' ).hasClass( 'rwmb-wysiwyg-wrapper' ) ){
			add_wysiwyg_clone( $input );
		}
		else
			add_cloned_fields( $input );

		toggle_remove_buttons( $input );

		return false;
	} );

	// Remove clones
	$( '.rwmb-input' ).on( 'click', '.remove-clone', function()
	{
		var $this = $( this ),
			$input = $this.parents( '.rwmb-input' ),
			$clone_group = $( this ).parents( '.rwmb-field' ).attr( 'clone-group' );

		// Remove clone only if there're 2 or more of them
		if ( $input.find( '.rwmb-clone' ).length <= 1 )
			return false;

		if ( $clone_group )
		{
			// Get the parent metabox and then find the matching
			// clone-group elements inside
			var $metabox = $( this ).parents( '.inside' );
			var $clone_group_list = $metabox.find( 'div[clone-group="' + $clone_group + '"]' );
			var $index = $this.parent().index();

			$.each( $clone_group_list.find( '.rwmb-input' ),
				function( key, value )
				{
					$( value ).children( '.rwmb-clone' ).eq( $index ).remove();

					// Toggle remove buttons
					toggle_remove_buttons( $( value ) );
				} );
		}
		else
		{
			$this.parent().remove();

			// Toggle remove buttons
			toggle_remove_buttons( $input );
		}

		return false;
	} );

	/**
	 * Hide remove buttons when there's only 1 of them
	 *
	 * @param $el jQuery element. If not supplied, the function will applies for all fields
	 *
	 * @return void
	 */
	function toggle_remove_buttons( $el )
	{
		var $button;
		if ( !$el )
			$el = $( '.rwmb-field' );
		$el.each( function()
		{
			$button = $( this ).find( '.remove-clone' );
			$button.length < 2 ? $button.hide() : $button.show();
		} );
	}
} );
