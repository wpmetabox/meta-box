jQuery( function ( $ ) {
	'use strict';

	function ButtonClick() {
		var $this = $( this ),
			$input = $this.find( 'input' ),
			$label = $input.parent(),
			type = $input.attr( 'type' );

		// Check initial status.
		if ( $input.prop( 'checked' ) ) {
			$label.addClass( 'selected' );
		}

		// Toggle status when click.
		$input.on( 'click', function () {
			if ( ! $input.prop( 'checked' ) ) {
				$label.removeClass( 'selected' );
				return;
			}

			$label.addClass( 'selected' );

			if ( 'radio' === type ) {
				$( '.rwmb-button-input-list li label' ).removeClass( 'selected' );
				$label.addClass( 'selected' );
			}
		} );
	}

	$( '.rwmb-button-input-list li' ).each( ButtonClick );
} );
