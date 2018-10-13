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
			this.autocomplete();
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
			latLng = L.latLng( defaultLoc[0], defaultLoc[1] ); // Initial position for map.

			this.map = L.map( this.canvas, {
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
				var latLng = L.latLng( location[0], location[1] );
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

			this.map.on( 'zoom', function () {
				that.updateCoordinate( that.marker.getLatLng() );
			} );

			this.marker.on( 'drag', function () {
				that.updateCoordinate( that.marker.getLatLng() );
			} );

			this.$findButton.on( 'click', function ( e ) {
				e.preventDefault();
				that.geocodeAddress();
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
			if ( ! this.map ) {
				return;
			}
			this.map.invalidateSize();
			this.map.panTo( this.map.getCenter() );
		},

		// Autocomplete address
		autocomplete: function () {
			var that = this,
				$address = this.getAddressField();

			if ( null === $address ) {
				return;
			}

			// If Meta Box Geo Location installed. Do not run auto complete.
			if ( $( '.rwmb-geo-binding' ).length ) {
				$address.on( 'selected_address', that.geocodeAddress );
				return;
			}

			$address.autocomplete( {
				source: function ( request, response ) {
					$.get( 'https://nominatim.openstreetmap.org/search', {
						format: 'json',
						q: request.term,
						countrycodes: that.$canvas.data( 'region' ),
						"accept-language": that.$canvas.data( 'language' )
					}, function( results ) {
						if ( ! results.length ) {
							response( [ {
								value: '',
								label: RWMB_Osm.no_results_string
							} ] );
							return;
						}
						response( results.map( function ( item ) {
							return {
								label: item.display_name,
								value: item.display_name,
								latitude: item.lat,
								longitude: item.lon
							};
						} ) );
					}, 'json' );
				},
				select: function ( event, ui ) {
					var latLng = L.latLng( ui.item.latitude, ui.item.longitude );

					that.map.panTo( latLng );
					that.marker.setLatLng( latLng );
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
			var address = this.getAddress(),
				that = this;
			if ( ! address ) {
				return;
			}

			$.get( 'https://nominatim.openstreetmap.org/search', {
				format: 'json',
				q: address,
				limit: 1,
				countrycodes: that.$canvas.data( 'region' ),
				"accept-language": that.$canvas.data( 'language' )
			}, function( result ) {
				if ( result.length !== 1 ) {
					return;
				}
				var latLng = L.latLng( result[0].lat, result[0].lon );
				that.map.panTo( latLng );
				that.marker.setLatLng( latLng );
				that.updateCoordinate( latLng );
			}, 'json' );
		},

		// Get the address field.
		getAddressField: function() {
			// No address field or more than 1 address fields, ignore
			if ( ! this.addressField || this.addressField.split( ',' ).length > 1 ) {
				return null;
			}
			return this.findAddressField( this.addressField );
		},

		// Get the address value for geocoding.
		getAddress: function() {
			var that = this;

			return this.addressField.split( ',' )
				.map( function( part ) {
					part = that.findAddressField( part );
					return null === part ? '' : part.val();
				} )
				.join( ',' ).replace( /\n/g, ',' ).replace( /,,/g, ',' );
		},

		// Find address field based on its name attribute. Auto search inside groups when needed.
		findAddressField: function( fieldName ) {
			// Not in a group.
			var $address = $( 'input[name="' + fieldName + '"]');
			if ( $address.length ) {
				return $address;
			}

			// If map and address is inside a cloneable group.
			$address = this.$container.closest( '.rwmb-group-clone' ).find( 'input[name*="[' + fieldName + ']"]' );
			if ( $address.length ) {
				return $address;
			}

			// If map and address is inside a non-cloneable group.
			$address = this.$container.closest( '.rwmb-group-wrapper' ).find( 'input[name*="[' + fieldName + ']"]' );
			if ( $address.length ) {
				return $address;
			}

			return null;
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