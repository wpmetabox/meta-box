jQuery( function ( $ ) {
	'use strict';

	// Check initial status.
	function CheckInput() {
		var $this 	= $( this ),
			$input 	= $this.find( 'input' ),
			$label 	= $input.parent(),
			type 	= $input.attr( 'type' );

		if ( $input.prop( 'checked' ) ) {
			$label.addClass( 'selected' );
		}
	}
	$( '.rwmb-button-input-list li' ).each( CheckInput );

	// Toggle status when click.
	function InputClick() {
		var $this 			= $( this ),
			$input 			= $this.find( 'input' ),
			$label 			= $input.parent(),
			type 			= $input.attr( 'type' ),
			parent_label 	= $( this ).parent().find( 'label' );

			if ( ! $input.prop( 'checked' ) ) {
				$label.removeClass( 'selected' );
				return;
			}

			$label.addClass( 'selected' );

			if ( 'radio' === type ) {
				parent_label.removeClass( 'selected' );
				$label.addClass( 'selected' );
			}
	}
	$( document ).on( 'click', '.rwmb-button-input-list li', InputClick );
	$( document ).on( 'clone', '.rwmb-button-input-list', CheckInput );
} );
