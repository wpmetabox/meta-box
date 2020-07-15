( function ( $, rwmb, i18n ) {
	'use strict';

	/**
	 * Transform an input into an autocomplete.
	 */
	function transform( e ) {
		var $this = $( this ),
			$search = $this.siblings( '.rwmb-autocomplete-search' ),
			$result = $this.siblings( '.rwmb-autocomplete-results' ),
			name = $this.attr( 'name' );

		// If the function is called on cloning, then change the field name and clear all results
		if ( e.hasOwnProperty( 'type' ) && 'clone' == e.type ) {
			$result.html( '' );
		}

		$search.removeClass( 'ui-autocomplete-input' ).autocomplete( {
			minLength: 0,
			source: $this.data( 'options' ),
			select: function ( event, ui ) {
				$result.append(
					'<div class="rwmb-autocomplete-result">' +
					'<div class="label">' + ( typeof ui.item.excerpt !== 'undefined' ? ui.item.excerpt : ui.item.label ) + '</div>' +
					'<div class="actions">' + i18n.delete + '</div>' +
					'<input type="hidden" class="rwmb-autocomplete-value" name="' + name + '" value="' + ui.item.value + '">' +
					'</div>'
				);

				// Reinitialize value.
				$search.val( '' ).trigger( 'change' );

				return false;
			}
		} );
	}

	function deleteSelection( e ) {
		e.preventDefault();
		var $item = $( this ).parent(),
			$search = $item.parent().siblings( '.rwmb-autocomplete-search' );

		$item.remove();
		$search.trigger( 'change' );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-autocomplete-wrapper input[type="hidden"]' ).each( transform );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-autocomplete', transform )
		.on( 'click', '.rwmb-autocomplete-result .actions', deleteSelection );
} )( jQuery, rwmb, RWMB_Autocomplete );
