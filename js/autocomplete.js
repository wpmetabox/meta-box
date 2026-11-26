( function ( $, rwmb, i18n ) {
	'use strict';

	/**
	 * Transform an input into an autocomplete.
	 */
	function transform( e ) {
		const $this = $( this ),
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
				const text = typeof ui.item.excerpt !== 'undefined' ? ui.item.excerpt : ui.item.label;

				$result.append(
					$( '<div class="rwmb-autocomplete-result">' ).append(
						$( '<div class="label">' ).text( text ),
						$( '<div class="actions">' ).text( i18n.delete ),
						$( '<input type="hidden" class="rwmb-autocomplete-value">' )
							.attr( 'name', name )
							.val( ui.item.value )
					)
				);

				// Reinitialize value.
				$search.val( '' ).trigger( 'change' );

				return false;
			}
		} );
	}

	function deleteSelection( e ) {
		e.preventDefault();
		const $item = $( this ).parent(),
			$search = $item.parent().siblings( '.rwmb-autocomplete-search' );

		$item.remove();
		$search.trigger( 'change' );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-autocomplete-wrapper input[type="hidden"]' ).each( transform );
		$( e.target ).find( '.rwmb-autocomplete-results' ).each( function () {
			$( this ).data( 'initial', this.innerHTML );
		} );
	}

	function reset( e ) {
		$( e.target ).find( '.rwmb-autocomplete-results' ).each( function () {
			$( this ).html( $( this ).data( 'initial' ) || '' );
		} );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'mb_reset', reset )
		.on( 'clone', '.rwmb-autocomplete', transform )
		.on( 'click', '.rwmb-autocomplete-result .actions', deleteSelection );
} )( jQuery, rwmb, RWMB_Autocomplete );
