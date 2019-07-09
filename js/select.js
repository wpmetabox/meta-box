( function ( $, document, rwmb ) {
	'use strict';

	/**
	 * Object stores all necessary methods for toggle all actions.
	 */
	var selectToggle = {
		run: function ( e ) {
			e.preventDefault();

			var $this = $( this ),
				$select = $this.parent().siblings( 'select' );

			if ( 'none' === $this.data( 'type' ) ) {
				$select.val( [] ).trigger( 'change' );
				return;
			}
			var selected = [];
			$select.find( 'option' ).each( function ( index, option ) {
				selected.push( option.value );
			} );
			$select.val( selected ).trigger( 'change' );
		},

		/**
		 * Add event listener for the toggle all link.
		 * Expect this = select element.
		 */
		bind: function () {
			$( this ).closest( '.rwmb-input' ).on( 'click', '.rwmb-select-all-none a', selectToggle.run );
		}
	};

	function init( e ) {
		$( e.target ).find( '.rwmb-select' ).each( selectToggle.bind );
	}

	$( document )
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-select', selectToggle.bind );

	// Export to use for select_advanced.
	rwmb.selectToggle = selectToggle;
} )( jQuery, document, rwmb );
