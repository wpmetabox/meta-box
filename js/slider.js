( function ( $, rwmb ) {
	'use strict';

	function transform() {
		var $input      = $( this ),
			$slider     = $input.siblings( '.rwmb-slider' ),
			$valueLabel = $slider.siblings( '.rwmb-slider-value-label' ).find( 'span' ),
			value       = $input.val(),
			options     = $slider.data( 'options' );

		$slider.html( '' );
		$valueLabel.text( value );

		if ( true === options.range ) {
			value = value.split( '|' );
			options.values = value;
		} else {
			options.value = value;
		}

		options.slide = function ( event, ui ) {
			var value = ui.value;
			if ( options.range === true ) {
				value = ui.values[ 0 ] + '|' + ui.values[ 1 ];
			}

			$input.val( value );
			$valueLabel.html( value );
		};

		$slider.slider( options );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-slider-value' ).each( transform );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-slider-value', transform );
} )( jQuery, rwmb );
