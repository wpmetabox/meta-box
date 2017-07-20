jQuery( function ( $ ) {
	'use strict';

	function rwmb_update_slider() {
		var $input = $( this ),
			$slider = $input.siblings( '.rwmb-slider' ),
			$valueLabel = $slider.siblings( '.rwmb-slider-value-label' ).find( 'span' ),
			value = $input.val(),
			options = $slider.data( 'options' );


		$slider.html( '' );

		if ( ! value ) {
			value = 0;
			$input.val( 0 );
			$valueLabel.text( '0' );
		}
		else {
			$valueLabel.text( value );
		}

		// Assign field value and callback function when slide
		options.value = value;
		options.slide = function ( event, ui ) {
			$input.val( ui.value );
			$valueLabel.text( ui.value );
		};

		$slider.slider( options );
	}

	$( '.rwmb-slider-value' ).each( rwmb_update_slider );
	$( document ).on( 'clone', '.rwmb-slider-value', rwmb_update_slider );
} );
