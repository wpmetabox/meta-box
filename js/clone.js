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

	// Add more clones
	$( '.add-clone' ).click( function ()
	{
		var	$input      = $( this ).parents( '.rwmb-input' );
			$clone_last = $input.find( '.rwmb-clone:last' ),
			$clone      = $clone_last.clone( true );

		$clone.insertAfter( $clone_last );

		// Reset value
		$clone.find( ':input' ).val( '' );

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

		return false;
	} );

	// Remove clones
	$( '.rwmb-input' ).delegate( '.remove-clone', 'click', function()
	{
		var $this  = $( this ),
			$input = $this.parents( '.rwmb-input' );

		// Remove clone only if there're 2 or more of them
		if ( $input.find( '.rwmb-clone' ).length > 1 )
		{
			$this.parent().remove();

			// Toggle remove buttons
			toggle_remove_buttons( $input );
		}

		return false;
	} );
} );