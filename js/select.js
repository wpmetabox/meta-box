jQuery( function ( $ ) {
	'use strict';

	/**
	 * Object stores all necessary methods for select All/None actions
	 * Assign to global variable so we can access to this object from select advanced field
	 */
	var select = {
		/**
		 * Select all/none for select tag
		 * @param event Click event.
		 */
		selectAllNone: function ( event ) {
			event.preventDefault();
			var $this = $( this ),
				$element = $this.parent().siblings( 'select' );

			if ( 'none' === $this.data( 'type' ) ) {
				$element.val( [] ).trigger( 'change' );
				return;
			}
			var selected = [];
			$element.find( 'option' ).each( function ( index, option ) {
				selected.push( option.value );
			} );
			$element.val( selected ).trigger( 'change' );
		},

		/**
		 * Add event listener for select all/none links when click
		 * @param $el jQuery select element
		 */
		bindEvents: function ( $el ) {
			$el.closest( '.rwmb-input' ).on( 'click', '.rwmb-select-all-none a', select.selectAllNone );
		}
	};

	/**
	 * Update select field when clicking clone button
	 */
	function update() {
		select.bindEvents( $( this ) );
	}

	// Run for select field.
	$( '.rwmb-select' ).each( update );
	$( document ).on( 'clone', '.rwmb-select', update );

	// Export to use for select_advanced.
	window.rwmbSelect = select;
} );
