( function ( $, L, rwmb, i18n ) {
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
			setTimeout( function () {
				map.invalidateSize();
			}, 200 );
		},

		// Initialize DOM elements
		initDomElements: function () {
			this.$canvas = this.$container.find( '.rwmb-osm-canvas' );
			this.canvas = this.$canvas[ 0 ];
			this.$coordinate = this.$container.find( '.rwmb-osm' );
			this.addressField = this.$container.data( 'address-field' );
		},

		setCenter: function ( location ) {
			this.map.panTo( location );
			if ( this.marker ) {
				this.marker.setLatLng( location );
				return;
			}

			this.marker = L.marker( location, {
				draggable: true
			} ).addTo( this.map );
		},

		initMapElements: function () {
			this.map = L.map( this.canvas, { zoom: 14, gestureHandling: true } );
			L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			} ).addTo( this.map );

			// If there is a saved location, don't set the default location.
			if ( this.$coordinate.val() ) {
				return;
			}

			// Load default location if it's set.
			const defaultLoc = this.$canvas.data( 'default-loc' );
			if ( defaultLoc ) {
				return this.setCenter( defaultLoc.split( ',' ) );
			}

			// Set default location to Dublin as a start.
			const dublin = [ 53.346881, -6.258860 ];
			this.setCenter( dublin );

			// Try to load current user location. Note that Geolocation API works only on HTTPS.
			if ( location.protocol.includes( 'https' ) && navigator.geolocation ) {
				this.map.locate( { setView: true } ).on( 'locationfound', e => this.setCenter( e.latlng ) );
			}
		},

		initMarkerPosition: function () {
			const coordinate = this.$coordinate.val();

			if ( coordinate ) {
				const location = coordinate.split( ',' );
				this.setCenter( location );

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

			// Custom event to refresh maps when in hidden divs.
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

			$address.autocomplete( {
				source: function ( request, response ) {
					$.get( 'https://nominatim.openstreetmap.org/search', {
						format: 'json',
						q: request.term,
						countrycodes: that.$canvas.data( 'region' ),
						"accept-language": that.$canvas.data( 'language' ),
						addressdetails: 1
					}, function ( results ) {
						if ( !results.length ) {
							response( [ {
								value: '',
								label: i18n.no_results_string
							} ] );
							return;
						}
						response( results.map( function ( item ) {
							return {
								address: item.address,
								label: item.display_name,
								value: item.display_name,
								latitude: item.lat,
								longitude: item.lon
							};
						} ) );
					}, 'json' );
				},
				select: function ( event, ui ) {
					const latLng = L.latLng( ui.item.latitude, ui.item.longitude );

					that.setCenter( latLng );
					that.updateCoordinate( latLng );

					$address.trigger( 'selected_address', [ ui.item ] );
				}
			} );
		},

		// Update coordinate to input field
		updateCoordinate: function ( latLng ) {
			var zoom = this.map.getZoom();
			this.$coordinate.val( latLng.lat + ',' + latLng.lng + ',' + zoom ).trigger( 'change' );
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
			$.get( 'https://nominatim.openstreetmap.org/search', {
				format: 'json',
				q: address,
				limit: 1,
				countrycodes: that.$canvas.data( 'region' ),
				"accept-language": that.$canvas.data( 'language' )
			}, function ( result ) {
				if ( result.length !== 1 ) {
					if ( notify ) {
						alert( i18n.no_results_string );
					}
					return;
				}
				var latLng = L.latLng( result[ 0 ].lat, result[ 0 ].lon );
				that.setCenter( latLng );
				that.updateCoordinate( latLng );
			}, 'json' );
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
			if ( !$groupWrapper.length ) {
				$groupWrapper = this.$container.closest( '.rwmb-group-wrapper' );
			}

			if ( !$groupWrapper.length ) {
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
			controller = $this.data( 'osmController' );
		if ( controller ) {
			return;
		}

		controller = new OsmField( $this );
		controller.init();
		$this.data( 'osmController', controller );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-osm-field' ).each( createController );
	}

	function restart() {
		$( '.rwmb-osm-field' ).each( createController );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-input', restart );
} )( jQuery, L, rwmb, RWMB_Osm );