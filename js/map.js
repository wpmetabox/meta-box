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
			this.canvas = this.$canvas[ 0 ];
			this.$coordinate = this.$container.find( '.rwmb-map' );
			this.addressField = this.$container.data( 'address-field' );
		},

		setCenter: function ( location ) {
			if ( !( location instanceof google.maps.LatLng ) ) {
				location = new google.maps.LatLng( parseFloat( location.lat ), parseFloat( location.lng ) );
			}
			this.map.setCenter( location );
			if ( this.marker ) {
				this.marker.position = location;
				return;
			}

			if ( !google.maps.marker || !google.maps.marker.AdvancedMarkerElement ) {
				return;
			}

			this.marker = new google.maps.marker.AdvancedMarkerElement( {
				position: location,
				map: this.map,
				gmpDraggable: this.$canvas.data( 'marker_draggable' ) === 'true' || this.$canvas.data( 'marker_draggable' ) === true,
			} );
		},

		initMapElements: function () {
			var mapOptions = {
				zoom: 14,
				streetViewControl: 0,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};

			mapOptions.mapId = this.$canvas.data( 'map_id' ) || 'DEMO_MAP_ID';

			this.map = new google.maps.Map( this.canvas, mapOptions );

			// If there is a saved location, don't set the default location.
			if ( this.$coordinate.val() ) {
				return;
			}

			// Load default location if it's set.
			let defaultLoc = this.$canvas.data( 'default-loc' );
			if ( defaultLoc ) {
				const [ lat, lng ] = defaultLoc.split( ',' );
				return this.setCenter( { lat, lng } );
			}

			// Set default location to Dublin as a start.
			const dublin = { lat: 53.346881, lng: -6.258860 };
			this.setCenter( dublin );

			// Try to load current user location. Note that Geolocation API works only on HTTPS.
			if ( location.protocol.includes( 'https' ) && navigator.geolocation ) {
				navigator.geolocation.getCurrentPosition( position => this.setCenter( { lat: position.coords.latitude, lng: position.coords.longitude } ) );
			}
		},

		initMarkerPosition: function () {
			const coordinate = this.$coordinate.val();

			if ( coordinate ) {
				const location = coordinate.split( ',' );
				this.setCenter( { lat: location[ 0 ], lng: location[ 1 ] } );

				const zoom = location.length > 2 ? parseInt( location[ 2 ], 10 ) : 14;
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
				var addressFields = this.addressField.split( ',' ).forEach( function ( part ) {
					var $field = that.findAddressField( part );
					if ( null !== $field ) {
						$field.on( 'change', geocodeAddress );
					}
				} );
			}

			google.maps.event.addListener( this.map, 'click', function ( event ) {
				if ( !that.marker ) {
					return;
				}
				that.marker.position = event.latLng;
				that.updateCoordinate( event.latLng );
			} );

			google.maps.event.addListener( this.map, 'zoom_changed', function ( event ) {
				if ( !that.marker ) {
					return;
				}
				var pos = that.marker.position;
				that.updateCoordinate( new google.maps.LatLng( pos.lat, pos.lng ) );
			} );

			if ( that.marker ) {
				that.marker.addEventListener( 'gmp-drag', function () {
					var pos = that.marker.position;
					that.updateCoordinate( new google.maps.LatLng( pos.lat, pos.lng ) );
				} );
			}

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
			if ( !this.map ) {
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
				source: async function ( request, response ) {
					var region = that.$canvas.data( 'region' );
					var requestOptions = { input: request.term };

					if ( region ) {
						requestOptions.includedRegionCodes = [ region ];
					}

					try {
						var result = await google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions( requestOptions );
						var suggestions = result.suggestions;

						if ( !suggestions || !suggestions.length ) {
							response( [ {
								value: '',
								label: i18n.no_results_string
							} ] );
							return;
						}

						response( suggestions.map( function ( suggestion ) {
							return {
								label: suggestion.placePrediction.text.text,
								value: suggestion.placePrediction.text.text,
								placeid: suggestion.placePrediction.placeId,
							};
						} ) );
					} catch ( error ) {
						console.error( 'AutocompleteSuggestion error:', error );
						response( [ {
							value: '',
							label: i18n.no_results_string
						} ] );
					}
				},
				select: function ( event, ui ) {
					geocoder.geocode( {
						'placeId': ui.item.placeid
					},
						function ( responses, status ) {
							if ( status == 'OK' ) {
								const latLng = new google.maps.LatLng( responses[ 0 ].geometry.location.lat(), responses[ 0 ].geometry.location.lng() );
								that.setCenter( latLng );
								that.updateCoordinate( latLng );
							}
						} );
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
			if ( !address ) {
				return;
			}

			if ( false !== notify ) {
				notify = true;
			}
			geocoder.geocode( { 'address': address }, function ( results, status ) {
				if ( status !== google.maps.GeocoderStatus.OK ) {
					if ( notify ) {
						alert( i18n.no_results_string );
					}
					return;
				}
				that.setCenter( results[ 0 ].geometry.location );
				that.updateCoordinate( results[ 0 ].geometry.location );
			} );
		},

		// Get the address field.
		getAddressField: function () {
			// No address field or more than 1 address fields, ignore
			if ( !this.addressField || this.addressField.split( ',' ).length > 1 ) {
				return null;
			}
			return this.findAddressField( this.addressField );
		},

		// Get the address value for geocoding.
		getAddress: function () {
			var that = this;

			return this.addressField.split( ',' )
				.map( function ( part ) {
					part = that.findAddressField( part );
					return null === part ? '' : part.val();
				} )
				.join( ',' ).replace( /\n/g, ',' ).replace( /,,/g, ',' );
		},

		// Find address field based on its name attribute. Auto search inside groups when needed.
		findAddressField: function ( fieldName ) {
			let selector = `input[name="${ fieldName }"], select[name="${ fieldName }"]`;

			// Not in a group.
			let $address = $( selector );
			if ( $address.length ) {
				return $address;
			}

			let $groupWrapper = this.$container.closest( '.rwmb-group-clone' );
			if ( ! $groupWrapper.length ) {
				$groupWrapper = this.$container.closest( '.rwmb-group-wrapper' );
			}

			if ( ! $groupWrapper.length ) {
				return null;
			}

			selector = `input[name*="${ fieldName }"], select[name*="${ fieldName }"]`;

			$address = $groupWrapper.find( selector );
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
