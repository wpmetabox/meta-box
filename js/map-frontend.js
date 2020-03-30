/* global google, jQuery */

jQuery( function ( $ ) {
	'use strict';

	/**
	 * Callback function for Google Maps Lazy Load library to display map
	 *
	 * @return void
	 */
	function displayMap() {
		var $container = $( this ),
			options = $container.data( 'map_options' );

		var mapOptions = options.js_options,
			center = new google.maps.LatLng( options.latitude, options.longitude ),
			map;

		switch ( mapOptions.mapTypeId ) {
			case 'ROADMAP':
				mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
				break;
			case 'SATELLITE':
				mapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
				break;
			case 'HYBRID':
				mapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
				break;
			case 'TERRAIN':
				mapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
				break;
		}
		mapOptions.center = center;

		// Typcast zoom to a number
		mapOptions.zoom *= 1;

		if ( typeof mapOptions.styles === 'string' ) {
			mapOptions.styles = JSON.parse(mapOptions.styles);
		}

		map = new google.maps.Map( this, mapOptions );

		// Set marker
		if ( options.marker ) {
			var marker = new google.maps.Marker( {
				position: center,
				map: map
			} );

			// Set marker title
			if ( options.marker_title ) {
				marker.setTitle( options.marker_title );
			}

			// Set marker icon
			if ( options.marker_icon ) {
				marker.setIcon( options.marker_icon );
			}
		}

		// Set info window
		if ( options.info_window ) {
			var infoWindow = new google.maps.InfoWindow( {
				content: options.info_window,
				minWidth: 200
			} );

			google.maps.event.addListener( marker, 'click', function () {
				infoWindow.open( map, marker );
			} );

			if ( true === mapOptions.openInfoWindow ) {
				infoWindow.open( map, marker );
			}
		}
	}

	// Loop through all map instances and display them
	$( '.rwmb-map-canvas' ).each( displayMap );
} );
