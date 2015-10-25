(function ( $ )
{
	'use strict';

	// Use function construction to store map & DOM elements separately for each instance
	var MapField = function ( $container )
	{
		this.$container = $container;
	};

	// Use prototype for better performance
	MapField.prototype = {
		// Initialize everything
		init              : function ()
		{
			this.initDomElements();
			this.initMapElements();

			this.initMarkerPosition();
			this.addListeners();
			this.autocomplete();
		},

		// Initialize DOM elements
		initDomElements   : function ()
		{
			this.canvas = this.$container.find( '.rwmb-map-canvas' )[0];
			this.$coordinate = this.$container.find( '.rwmb-map-coordinate' );
			this.$findButton = this.$container.find( '.rwmb-map-goto-address-button' );
			this.addressField = this.$findButton.val();
		},

		// Initialize map elements
		initMapElements   : function ()
		{
			var defaultLoc = $( this.canvas ).data( 'default-loc' ),
				latLng;

			defaultLoc = defaultLoc ? defaultLoc.split( ',' ) : [53.346881, -6.258860];
			latLng = new google.maps.LatLng( defaultLoc[0], defaultLoc[1] ); // Initial position for map

			this.map = new google.maps.Map( this.canvas, {
				center           : latLng,
				zoom             : 14,
				streetViewControl: 0,
				mapTypeId        : google.maps.MapTypeId.ROADMAP
			} );
			this.marker = new google.maps.Marker( { position: latLng, map: this.map, draggable: true } );
			this.geocoder = new google.maps.Geocoder();
		},

		// Initialize marker position
		initMarkerPosition: function ()
		{
			var coord = this.$coordinate.val(),
				l,
				zoom;

			if ( coord )
			{
				l = coord.split( ',' );
				this.marker.setPosition( new google.maps.LatLng( l[0], l[1] ) );

				zoom = l.length > 2 ? parseInt( l[2], 10 ) : 14;

				this.map.setCenter( this.marker.position );
				this.map.setZoom( zoom );
			}
			else if ( this.addressField )
			{
				this.geocodeAddress();
			}
		},

		// Add event listeners for 'click' & 'drag'
		addListeners      : function ()
		{
			var that = this;
			google.maps.event.addListener( this.map, 'click', function ( event )
			{
				that.marker.setPosition( event.latLng );
				that.updateCoordinate( event.latLng );
			} );
			google.maps.event.addListener( this.marker, 'drag', function ( event )
			{
				that.updateCoordinate( event.latLng );
			} );

			this.$findButton.on( 'click', function ()
			{
				that.geocodeAddress();
				return false;
			} );

			/**
			 * Add a custom event that allows other scripts to refresh the maps when needed
			 * For example: when maps is in tabs or hidden div (this is known issue of Google Maps)
			 *
			 * @see https://developers.google.com/maps/documentation/javascript/reference
			 *      ('resize' Event)
			 */
			$( window ).on( 'rwmb_map_refresh', function()
			{
				if ( that.map )
				{
					google.maps.event.trigger( that.map, 'resize' );
				}
			} );
		},

		// Autocomplete address
		autocomplete      : function ()
		{
			var that = this;

			// No address field or more than 1 address fields, ignore
			if ( !this.addressField || this.addressField.split( ',' ).length > 1 )
			{
				return;
			}

			$( '#' + this.addressField ).autocomplete( {
				source: function ( request, response )
				{
					that.geocoder.geocode( {
						'address': request.term
					}, function ( results )
					{
						response( $.map( results, function ( item )
						{
							return {
								label    : item.formatted_address,
								value    : item.formatted_address,
								latitude : item.geometry.location.lat(),
								longitude: item.geometry.location.lng()
							};
						} ) );
					} );
				},
				select: function ( event, ui )
				{
					var latLng = new google.maps.LatLng( ui.item.latitude, ui.item.longitude );

					that.map.setCenter( latLng );
					that.marker.setPosition( latLng );
					that.updateCoordinate( latLng );
				}
			} );
		},

		// Update coordinate to input field
		updateCoordinate  : function ( latLng )
		{
			this.$coordinate.val( latLng.lat() + ',' + latLng.lng() );
		},

		// Find coordinates by address
		// Find coordinates by address
		geocodeAddress    : function ()
		{
			var address,
				addressList = [],
				fieldList = this.addressField.split( ',' ),
				loop,
				that = this;

			for ( loop = 0; loop < fieldList.length; loop++ )
			{
				addressList[loop] = jQuery( '#' + fieldList[loop] ).val();
			}

			address = addressList.join( ',' ).replace( /\n/g, ',' ).replace( /,,/g, ',' );

			if ( address )
			{
				this.geocoder.geocode( { 'address': address }, function ( results, status )
				{
					if ( status === google.maps.GeocoderStatus.OK )
					{
						that.map.setCenter( results[0].geometry.location );
						that.marker.setPosition( results[0].geometry.location );
						that.updateCoordinate( results[0].geometry.location );
					}
				} );
			}
		}
	};

	$( function ()
	{
		$( '.rwmb-map-field' ).each( function ()
		{
			var field = new MapField( $( this ) );
			field.init();

			$( this ).data( 'mapController', field );

		} );

		$( '.rwmb-input' ).on( 'clone', function ()
		{
			$( '.rwmb-map-field' ).each( function ()
			{
				var field = new MapField( $( this ) );
				field.init();

				$( this ).data( 'mapController', field );
			} );
		} );
	} );

})( jQuery );
