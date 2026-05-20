/* global google, jQuery */

( function( $, document ) {
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

		// Typecast zoom to a number
		mapOptions.zoom *= 1;

		if ( typeof mapOptions.styles === 'string' ) {
			mapOptions.styles = JSON.parse(mapOptions.styles);
		}

		if ( mapOptions.mapId === undefined && options.map_id ) {
			mapOptions.mapId = options.map_id;
		}

		map = new google.maps.Map( this, mapOptions );

		// Set marker
		if ( options.marker ) {
			var markerOptions = {
				position: center,
				map: map
			};

			// Set marker title
			if ( options.marker_title ) {
				markerOptions.title = options.marker_title;
			}

			// Set marker icon via custom content
			if ( options.marker_icon ) {
				var icon = document.createElement( 'img' );
				icon.src = options.marker_icon;
				markerOptions.content = icon;
			}

			if ( options.info_window ) {
				markerOptions.gmpClickable = true;
			}

			var marker = new google.maps.marker.AdvancedMarkerElement( markerOptions );
		}

		// Set info window
		if ( options.info_window ) {
			var infoWindow = new google.maps.InfoWindow( {
				content: options.info_window,
				minWidth: 200
			} );

			marker.addEventListener( 'gmp-click', function () {
				infoWindow.open( { anchor: marker, map: map } );
			} );

			if ( true === mapOptions.openInfoWindow ) {
				infoWindow.open( { anchor: marker, map: map } );
			}
		}
	}

	// Loop through all map instances and display them
	$( '.rwmb-map-canvas' ).each( displayMap );
    
    $( document ).on( 'mb_blocks_preview', function( e ) {
        $( e.target )
                .find( ".rwmb-map-canvas" )
                .each( displayMap );
    } );    
} )( jQuery, document );
