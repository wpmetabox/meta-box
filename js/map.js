( function ( $, document, window, google, rwmb, i18n ) {
	'use strict';

	// Use function construction to store map & DOM elements separately for each instance
	var MapField = function ( $container ) {
		this.$container = $container;
	};

	// Geocoder service.
	var geocoder = new google.maps.Geocoder();

	// Use prototype for better performance
	MapField.prototype = {
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
			this.$canvas = this.$container.find( '.rwmb-map-canvas' );
			this.canvas = this.$canvas[0];
			this.$coordinate = this.$container.find( '.rwmb-map' );
			this.addressField = this.$container.data( 'address-field' );
		},

		// Initialize map elements
		initMapElements: function () {
			var defaultLoc = this.$canvas.data( 'default-loc' ),
				latLng;

			defaultLoc = defaultLoc ? defaultLoc.split( ',' ) : [53.346881, - 6.258860];
			latLng = new google.maps.LatLng( defaultLoc[0], defaultLoc[1] ); // Initial position for map

			this.map = new google.maps.Map( this.canvas, {
				center: latLng,
				zoom: 14,
				streetViewControl: 0,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			} );
			this.marker = new google.maps.Marker( {position: latLng, map: this.map, draggable: true} );
		},

		// Initialize marker position
		initMarkerPosition: function () {
			var coordinate = this.$coordinate.val(),
				location,
				zoom;

			if ( coordinate ) {
				location = coordinate.split( ',' );
				this.marker.setPosition( new google.maps.LatLng( location[0], location[1] ) );

				zoom = location.length > 2 ? parseInt( location[2], 10 ) : 14;

				this.map.setCenter( this.marker.position );
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

			google.maps.event.addListener( this.map, 'click', function ( event ) {
				that.marker.setPosition( event.latLng );
				that.updateCoordinate( event.latLng );
			} );

			google.maps.event.addListener( this.map, 'zoom_changed', function ( event ) {
				that.updateCoordinate( that.marker.getPosition() );
			} );

			google.maps.event.addListener( this.marker, 'drag', function ( event ) {
				that.updateCoordinate( event.latLng );
			} );

			/**
			 * Custom event to refresh maps when in hidden divs.
			 * @see https://developers.google.com/maps/documentation/javascript/reference ('resize' Event)
			 */
			var refresh = that.refresh.bind( this );
			$( window ).on( 'rwmb_map_refresh', refresh );

			// Refresh on meta box hide and show
			rwmb.$document.on( 'postbox-toggled', refresh );
			// Refresh on sorting meta boxes
			$( '.meta-box-sortables' ).on( 'sortstop', refresh );
		},

		refresh: function () {
			if ( ! this.map ) {
				return;
			}
			var zoom = this.map.getZoom(),
				center = this.map.getCenter();

			google.maps.event.trigger( this.map, 'resize' );
			this.map.setZoom( zoom );
			this.map.panTo( center );
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
					var options = {
						'address': request.term,
						'region': that.$canvas.data( 'region' )
					};
					geocoder.geocode( options, function ( results ) {
						if ( ! results.length ) {
							response( [ {
								value: '',
								label: i18n.no_results_string
							} ] );
							return;
						}
						response( results.map( function ( item ) {
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
					that.map.setCenter( latLng );
					that.marker.setPosition( latLng );
					that.updateCoordinate( latLng );
				}
			} );
		},

		// Update coordinate to input field
		updateCoordinate: function ( latLng ) {
			var zoom = this.map.getZoom();
			this.$coordinate.val( latLng.lat() + ',' + latLng.lng() + ',' + zoom ).trigger( 'change' );
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
			geocoder.geocode( {'address': address}, function ( results, status ) {
				if ( status !== google.maps.GeocoderStatus.OK ) {
					if ( notify ) {
						alert( i18n.no_results_string );
					}
					return;
				}
				that.map.setCenter( results[0].geometry.location );
				that.marker.setPosition( results[0].geometry.location );
				that.updateCoordinate( results[0].geometry.location );
			} );
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

	function createController() {
		var $this = $( this ),
			controller = $this.data( 'mapController' );
		if ( controller ) {
			return;
		}

		controller = new MapField( $this );
		controller.init();
		$this.data( 'mapController', controller );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-map-field' ).each( createController );
	}

	function restart() {
		$( '.rwmb-map-field' ).each( createController );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-input', restart );
} )( jQuery, document, window, google, rwmb, RWMB_Map );
