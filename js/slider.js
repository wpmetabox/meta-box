jQuery( function ( $ ) {
	'use strict';

	function rwmb_update_slider() {
		var $input      = $( this ),
			$slider     = $input.siblings( '.rwmb-slider' ),
			$valueLabel = $slider.siblings( '.rwmb-slider-value-label' ).find( 'span' ),
			value       = $input.val(),
			value_laber = '',
			options     = $slider.data( 'options' );

		$slider.html( '' );

		if ( ! value ) {
			value = 0;
			$input.val( 0 );
			$valueLabel.text( '0' );
		}
		else {
			value_laber = options.range ? value.replace( '|' , ' - ' ) : value;
			$valueLabel.text( value_laber );
		}

		value         = options.range ? value.split('|') : value;
		options.values = value;

		options.slide = function ( event, ui ) {
			if ( options.range == true ) {
				$input.val( ui.values[ 0 ] + "|" + ui.values[ 1 ] );
				$valueLabel.html( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
			} else {
				$input.val( ui.value );
				$valueLabel.html( ui.value );
			}
		};

		$slider.slider( options );
	}

	$( '.rwmb-slider-value' ).each( rwmb_update_slider );
	$( document ).on( 'clone', '.rwmb-slider-value', rwmb_update_slider );
} );
