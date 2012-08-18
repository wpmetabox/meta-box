jQuery( document ).ready( function ($)
{
	/**
	 * Hide remove buttons when there's only 1 of them
	 *
	 * @param $el jQuery element. If not supplied, the function will applies for all fields
	 *
	 * @return void
	 */
	function toggle_remove_buttons( $el )
	{
		if ( ! $el )
			$el = $( '.rwmb-field' );
		$el.each( function()
		{
			var $remove_buttons = $( this ).find( '.remove-clone' );
			if ( $remove_buttons.length < 2 )
				$remove_buttons.hide();
			else
				$remove_buttons.show();
		} );
	}

	// Call it on first run
	toggle_remove_buttons();

	function add_cloned_fields( $input )
	{
		var	$clone_last = $input.find( '.rwmb-clone:last' ),
			$clone      = $clone_last.clone( true );

		$clone.insertAfter( $clone_last );

		// Reset value
		$clone.find( ':input' ).val( '' );

		// Get the field name, and increment
		var $index = $clone.find( ':input' ).attr("name").replace(/\[([0-9]+)\]$/, 
			function(str, p1, p2, p3) 
			{
				return mystr = '[' + (parseInt(p1) +1) + ']';
			});

		// Update the "name" attribute
		$clone.find( ':input' ).attr("name", $index);

		// Toggle remove buttons
		toggle_remove_buttons( $input );

		// Fix color picker
		if ( 'function' === typeof rwmb_update_color_picker )
			rwmb_update_color_picker();
		
		// Fix date picker
		if ( 'function' === typeof rwmb_update_date_picker )
			rwmb_update_date_picker();
			
		// Fix time picker
		if ( 'function' === typeof rwmb_update_time_picker )
			rwmb_update_time_picker();
		
		// Fix datetime picker
		if ( 'function' === typeof rwmb_update_datetime_picker )
			rwmb_update_datetime_picker();
	}

	// Add more clones
	$( '.add-clone' ).click( function ()
	{
		var	$input       = $( this ).parents( '.rwmb-input' );
		var	$clone_group = $( this ).parents( '.rwmb-field' ).attr("clone-group");

		// If the field is part of a clone group, get all fields in that
		// group and itterate over them
		if ( $clone_group )
		{
			// Get the parent metabox and then find the matching
			// clone-group elements inside 
			var	$metabox          = $( this ).parents( '.inside' );
			var	$clone_group_list = $metabox.find( 'div[clone-group="' + $clone_group + '"]' );

			$.each($clone_group_list.find( '.rwmb-input' ), 
				function(key, value) { 
					add_cloned_fields( $(value) );
					});
		}
		else
			add_cloned_fields( $input );
		
		return false;
	} );

	// Remove clones
	$( '.rwmb-input' ).delegate( '.remove-clone', 'click', function()
	{
		var $this  = $( this ),
			$input = $this.parents( '.rwmb-input' );
		var	$clone_group = $( this ).parents( '.rwmb-field' ).attr("clone-group");

		// Remove clone only if there're 2 or more of them
		if ( $input.find( '.rwmb-clone' ).length > 1 )
		{	
			if ( $clone_group )
			{
				// Get the parent metabox and then find the matching
				// clone-group elements inside 
				var	$metabox          = $( this ).parents( '.inside' );
				var	$clone_group_list = $metabox.find( 'div[clone-group="' + $clone_group + '"]' );
				var	$index = $this.parent().index();
				
				$.each($clone_group_list.find( '.rwmb-input' ), 
					function(key, value) {
						$(value).children( '.rwmb-clone' ).eq($index).remove();
						
						// Toggle remove buttons
						toggle_remove_buttons( $(value) );
						});
			}
			else
			{
				$this.parent().remove();

				// Toggle remove buttons
				toggle_remove_buttons( $input );
			}
		}

		return false;
	} );
} );