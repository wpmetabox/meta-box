jQuery( function ( $ ) {
	'use strict';

	function update() {
		var $this = $( this ),
			$input = $this.find( 'input' ),
			$label = $input.parent();

		if ( $input.prop( 'checked' ) ) {
			$label.addClass( 'selected' );
		} else {
			$label.removeClass( 'selected' );
		}
	}

	function clickHandler() {
		var $this = $( this ),
			$input = $this.find( 'input' ),
			$label = $input.parent(),
			type = $input.attr( 'type' ),
			$allLabels = $this.parent().find( 'label' );
		if ( ! $input.prop( 'checked' ) ) {
			$label.removeClass( 'selected' );
			return;
		}
		$label.addClass( 'selected' );

		if ( 'radio' === type ) {
			$allLabels.removeClass( 'selected' );
			$label.addClass( 'selected' );
		}
	}

	$( '.rwmb-button-input-list li' ).each( update );
	$( document ).on( 'click', '.rwmb-button-input-list li', clickHandler );
	$( document ).on( 'clone', '.rwmb-button-input-list', update );
} );
