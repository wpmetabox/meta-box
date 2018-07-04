( function( $, L ) {
	'use strict';

	var osmTileLayer = L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
	} );

	// Use function construction to store map & DOM elements separately for each instance
	var OsmField = function ( $container ) {
		this.$container = $container;
	};

	// Use prototype for better performance
	OsmField.prototype = {
		// Initialize everything
		init: function () {
			this.initDomElements();
			this.initMapElements();

			this.initMarkerPosition();
			this.addListeners();
			// this.autocomplete();
		},

		// Initialize DOM elements
		initDomElements: function () {
			this.$canvas = this.$container.find( '.rwmb-osm-canvas' );
			this.canvas = this.$canvas[0];
			this.$coordinate = this.$container.find( '.rwmb-osm-coordinate' );
			this.$findButton = this.$container.find( '.rwmb-osm-goto-address-button' );
			this.addressField = this.$container.data( 'address-field' );
		},

		// Initialize map elements
		initMapElements: function () {
			var defaultLoc = this.$canvas.data( 'default-loc' ),
				latLng;

			defaultLoc = defaultLoc ? defaultLoc.split( ',' ) : [53.346881, -6.258860];
			latLng = new L.LatLng( defaultLoc[0], defaultLoc[1] ); // Initial position for map.

			this.map = new L.Map( this.canvas, {
				center: latLng,
				zoom: 14
			} );
			this.map.addLayer( osmTileLayer );
			this.marker = L.marker( latLng, {
				draggable: true
			} ).addTo( this.map );
		},

		// Initialize marker position
		initMarkerPosition: function () {
			var coordinate = this.$coordinate.val(),
				location,
				zoom;

			if ( coordinate ) {
				location = coordinate.split( ',' );
				var latLng = new L.latLng( location[0], location[1] );
				this.marker.setLatLng( latLng );

				zoom = location.length > 2 ? parseInt( location[2], 10 ) : 14;

				this.map.panTo( latLng );
				this.map.setZoom( zoom );
			} else if ( this.addressField ) {
				this.geocodeAddress();
			}
		},

		// Add event listeners for 'click' & 'drag'
		addListeners: function () {
			var that = this;
			this.map.on( 'click', function ( event ) {
				that.marker.setLatLng( event.latlng );
				that.updateCoordinate( event.latlng );
			} );

			this.map.on( 'zoom', function ( event ) {
				that.updateCoordinate( that.marker.getLatLng() );
			} );

			this.marker.on( 'drag', function ( event ) {
				that.updateCoordinate( that.marker.getLatLng() );
			} );

			this.$findButton.on( 'click', function () {
				that.geocodeAddress();
				return false;
			} );

			/**
			 * Add a custom event that allows other scripts to refresh the maps when needed
			 * For example: when maps is in tabs or hidden div (this is known issue of Google Maps)
			 *
			 * @see https://developers.google.com/maps/documentation/javascript/reference ('resize' Event)
			 */
			$( window ).on( 'rwmb_map_refresh', function () {
				that.refresh();
			} );

			// Refresh on meta box hide and show
			$( document ).on( 'postbox-toggled', function () {
				that.refresh();
			} );
			// Refresh on sorting meta boxes
			$( '.meta-box-sortables' ).on( 'sortstop', function () {
				that.refresh();
			} );
		},

		refresh: function () {
			var zoom = this.map.getZoom(),
				center = this.map.getCenter();

			if ( this.map ) {
				google.maps.event.trigger( this.map, 'resize' );
				this.map.setZoom( zoom );
				this.map.panTo( center );
			}
		},

		// Autocomplete address
		autocomplete: function () {
			var that = this;

			// No address field or more than 1 address fields, ignore
			if ( ! this.addressField || this.addressField.split( ',' ).length > 1 ) {
				return;
			}

			var $address = $( 'input[name="' + this.addressField + '"]');

			// If map and address is inside a group, the input name of address field is changed.
			if ( 0 === $address.length ) {
				$address = this.$container.closest( '.rwmb-group-wrapper' ).find( 'input[name*="[' + this.addressField + ']"]' );
			}

			// If Meta Box Geo Location installed. Do not run auto complete.
			if ( $( '.rwmb-geo-binding' ).length ) {
				$address.on( 'selected_address', function () {
					that.$findButton.trigger( 'click' );
				} );

				return false;
			}

			$address.autocomplete( {
				source: function ( request, response ) {
					var options = {
						'address': request.term,
						'region': that.$canvas.data( 'region' )
					};
					geocoder.geocode( options, function ( results ) {
						response( $.map( results, function ( item ) {
							return {
								label: item.formatted_address,
								value: item.formatted_address,
								latitude: item.geometry.location.lat(),
								longitude: item.geometry.location.lng()
							};
						} ) );
					} );
				},
				select: function ( event, ui ) {
					var latLng = new google.maps.LatLng( ui.item.latitude, ui.item.longitude );

					that.map.panTo( latLng );
					that.marker.setPosition( latLng );
					that.updateCoordinate( latLng );
				}
			} );
		},

		// Update coordinate to input field
		updateCoordinate: function ( latLng ) {
			var zoom = this.map.getZoom();
			this.$coordinate.val( latLng.lat + ',' + latLng.lng + ',' + zoom );
		},

		// Find coordinates by address
		geocodeAddress: function () {
			var address,
				addressList = [],
				fieldList = this.addressField.split( ',' ),
				loop,
				that = this;

			for ( loop = 0; loop < fieldList.length; loop ++ ) {
				addressList[loop] = $( '#' + fieldList[loop] ).val();
			}

			address = addressList.join( ',' ).replace( /\n/g, ',' ).replace( /,,/g, ',' );

			if ( ! address ) {
				return;
			}

			geocoder.geocode( {'address': address}, function ( results, status ) {
				if ( status !== google.maps.GeocoderStatus.OK ) {
					return;
				}
				that.map.panTo( results[0].geometry.location );
				that.marker.setPosition( results[0].geometry.location );
				that.updateCoordinate( results[0].geometry.location );
			} );
		}
	};

	function update() {
		$( '.rwmb-osm-field' ).each( function () {
			var $this = $( this ),
				controller = $this.data( 'osmController' );
			if ( controller ) {
				return;
			}

			controller = new OsmField( $this );
			controller.init();
			$this.data( 'osmController', controller );
		} );
	}

	$( function () {
		update();
		$( '.rwmb-input' ).on( 'clone', update );
	} );

} )( jQuery, L );