jQuery( document ).ready( function( $ )
{
	toggle_remove_buttons();

	function add_cloned_fields( $input )
	{
		var field_name = $input.find( '.rwmb-field-name' ).val(),
			template = $input.find( '#' + field_name + '_template' ).html(),
			$clone_last = $input.find( '.rwmb-clone:last' ),
			data={},
			template_html;
			
		data[field_name + '_index'] = $clone_last.data('field-index') + 1;
		template_html = _.template( template, data, {
			evaluate:    /<#([\s\S]+?)#>/g,
			interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
			escape:      /\{\{([^\}]+?)\}\}(?!\})/g
		} );			
		
		$( template_html ).insertAfter( $clone_last );		
		
		// Toggle remove buttons
		toggle_remove_buttons( $input );
		
		//Trigger custom clone event
		$input.trigger( 'clone' );
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
		else
			add_cloned_fields( $input );
		
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