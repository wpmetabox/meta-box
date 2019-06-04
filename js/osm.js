( function( $, L, i18n ) {
	'use strict';

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

			// Make sure the map is displayed fully.
			var map = this.map;
			setTimeout( function() {
				map.invalidateSize();
			}, 0 );
		},

		// Initialize DOM elements
		initDomElements: function () {
			this.$canvas = this.$container.find( '.rwmb-osm-canvas' );
			this.canvas = this.$canvas[0];
			this.$coordinate = this.$container.find( '.rwmb-osm-coordinate' );
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


			L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			} ).addTo( this.map );
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
				this.geocodeAddress( false );
			}
		},

		// Add event listeners for 'click' & 'drag'
		addListeners: function () {
			var that = this;

			/*
			 * Auto change the map when there's change in address fields.
			 * Works only for multiple address fields as single address field has autocomplete functionality.
			 */
			if ( this.addressField.split( ',' ).length > 1 ) {
				var geocodeAddress = that.geocodeAddress.bind( that );
				var addressFields = this.addressField.split( ',' ).forEach( function( part ) {
					var $field = that.findAddressField( part );
					if ( null !== $field ) {
						$field.on( 'change', geocodeAddress );
					}
				} );
			}

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

			/**
			 * Add a custom event that allows other scripts to refresh the maps when needed
			 * For example: when maps is in tabs or hidden div (this is known issue of Google Maps)
			 *
			 * @see https://developers.google.com/maps/documentation/javascript/reference ('resize' Event)
			 */
			$( window ).on( 'rwmb_map_refresh', that.refresh );

			// Refresh on meta box hide and show
			$( document ).on( 'postbox-toggled', that.refresh );
			// Refresh on sorting meta boxes
			$( '.meta-box-sortables' ).on( 'sortstop', that.refresh );
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

			// If Meta Box Geo Location installed. Do not run autocomplete.
			if ( $( '.rwmb-geo-binding' ).length ) {
				var geocodeAddress = that.geocodeAddress.bind( that );
				$address.on( 'selected_address', geocodeAddress );
				return false;
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
								label: i18n.no_results_string
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
		geocodeAddress: function ( notify ) {
			var address = this.getAddress(),
				that = this;
			if ( ! address ) {
				return;
			}

			if ( false !== notify ) {
				notify = true;
			}
			$.get( 'https://nominatim.openstreetmap.org/search', {
				format: 'json',
				q: address,
				limit: 1,
				countrycodes: that.$canvas.data( 'region' ),
				"accept-language": that.$canvas.data( 'language' )
			}, function( result ) {
				if ( result.length !== 1 ) {
					if ( notify ) {
						alert( i18n.no_results_string );
					}
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

} )( jQuery, L, RWMB_Osm );