jQuery( function( $ ) {
	'use strict';

	var osmTileLayer = L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
	} );

	/**
	 * Display Open Street Map
	 */
	function displayMap() {
		var options = $( this ).data( 'osm_options' ),
			mapOptions = options.js_options,
			center = L.latLng( options.latitude, options.longitude ),
			map;

			mapOptions.center = center;

			// Typcast zoom to a number
			mapOptions.zoom *= 1;

			map = L.map( this, mapOptions );
			map.addLayer( osmTileLayer );

		// Set marker
		if ( options.marker ) {
			var markerOptions = {};

			// Set marker title
			if ( options.marker_title ) {
				markerOptions.title = options.marker_title;
			}

			// Set marker icon
			if ( options.marker_icon ) {
				markerOptions.icon = L.icon( options.marker_icon );
			}

			var marker = L.marker( center, markerOptions ).addTo( map )
		}

		// Set info window
		if ( options.info_window ) {
			marker.bindPopup( options.info_window ).openPopup();
		}
	}

	// Loop through all map instances and display them
	$( '.rwmb-osm-canvas' ).each( displayMap );
} );