jQuery( function ( $ )
{
	'use strict';

	/**
	 * Update date picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function updateAutocomplete( e )
	{
		var $this = $( this ),
			$result = $this.next(),
			name = $this.data( 'name' );

		// If the function is called on cloning, then change the field name and clear all results
		// @see clone.js
		if ( e.hasOwnProperty( 'type' ) && 'clone' == e.type )
		{
			name = name.replace( /\[(\d+)\]/, function ( match, p1 )
			{
				return '[' + ( parseInt( p1, 10 ) + 1 ) + ']';
			} );

			// Update the "data-name" attribute for further cloning
			$this.attr( 'data-name', name );

			// Clear all results
			$result.html( '' );
		}

		$this.removeClass( 'ui-autocomplete-input' ).attr( 'id', '' )
			.autocomplete( {
			minLength: 0,
			source   : $this.data( 'options' ),
			select   : function ( event, ui )
			{
				$result.append(
					'<div class="rwmb-autocomplete-result">' +
					'<div class="label">' + ui.item.label + '</div>' +
					'<div class="actions">' + RWMB_Autocomplete.delete + '</div>' +
					'<input type="hidden" class="rwmb-autocomplete-value" name="' + name + '" value="' + ui.item.value + '">' +
					'</div>'
				);

				// Reinitialize value
				this.value = '';

				return false;
			}
		} );
	}

	$( '.rwmb-autocomplete-wrapper input[type="text"]' ).each( updateAutocomplete );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-autocomplete', updateAutocomplete );

	// Handle remove action
	$( document ).on( 'click', '.rwmb-autocomplete-result .actions', function ()
	{
		// remove result
		$( this ).parent().remove();
	} );
} );