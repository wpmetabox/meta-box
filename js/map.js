(function($)
{
	var mapField = {};

	(function()
	{
		"use strict";

		var thisMapField = this;

		this.container = null;
		this.canvas = null;
		this.latlng = null;
		this.map = null;
		this.marker = null;
		this.geocoder = null;

		this.init = function($container)
		{
			this.container = $container;
			this.canvas = $container.find('.rwmb-map-canvas');
			this.initLatLng(53.346881, -6.258860);
			this.initMap();
			this.initMarker();
			this.initGeocoder();
			this.initMarkerPosition();
			this.initListeners();
			this.initAutoComplete();
			this.bindHandlers();
		}

		this.initLatLng = function($lat, $lng)
		{
			this.latlng = new window.google.maps.LatLng($lat, $lng);
		}

		this.initMap = function()
		{
	 		this.map = new window.google.maps.Map(this.canvas[0], {
				zoom: 8,
				center: this.latlng,
				streetViewControl: 0,
				mapTypeId: window.google.maps.MapTypeId.ROADMAP
			});
		}

		this.initMarker = function() 
		{
			this.marker = new window.google.maps.Marker({position: this.latlng, map: this.map, draggable: true});
		}

		this.initMarkerPosition = function()
		{
			var coord = this.container.find('.rwmb-map-coordinate').val();
			var addressField = this.container.find('.rwmb-map-goto-address-button').val();
			var l;
			var zoom;

			if (coord)
			{
				l = coord.split( ',' );
				this.marker.setPosition( new window.google.maps.LatLng( l[0], l[1] ) );

				zoom = l.length > 2 ? parseInt( l[2], 10 ) : 15;

				this.map.setCenter(this.marker.position);
				this.map.setZoom(zoom);
			}
			else if (addressField)
			{
				this.geocodeAddress(addressField);
			}

		}

		this.initGeocoder = function()
		{
			this.geocoder = new window.google.maps.Geocoder();
		}

		this.initListeners = function()
		{
			var that = thisMapField;
			window.google.maps.event.addListener(this.map, 'click', function (event)
			{
				that.marker.setPosition(event.latLng);
				that.updatePositionInput(event.latLng);
			});
			window.google.maps.event.addListener(this.marker, 'drag', function (event)
			{
				that.updatePositionInput(event.latLng);
			});
		}

		this.updatePositionInput = function(latLng)
		{
			this.container.find('.rwmb-map-coordinate').val(latLng.lat() + ',' + latLng.lng());
		}

		this.geocodeAddress = function(addressField)
		{
			var address = '';
			var fieldList = addressField.split(',');
			var loop;

			for (loop = 0; loop < fieldList.length; loop++)
			{
				address += jQuery('#' + fieldList[loop] ).val();
				if(loop+1 < fieldList.length) {  address += ', '; }
			}

			address = address.replace( /\n/g, ',' );
			address = address.replace( /,,/g, ',' );

			var that = thisMapField;
			this.geocoder.geocode({'address': address}, function (results, status)
			{
				if ( status == window.google.maps.GeocoderStatus.OK )
				{
					that.updatePositionInput(results[0].geometry.location);
					that.marker.setPosition(results[0].geometry.location);
					that.map.setCenter(that.marker.position);
					that.map.setZoom(15);
				}
			});
		}

		this.initAutoComplete = function()
		{
			var addressField = this.container.find('.rwmb-map-goto-address-button').val();
			if (!addressField) return null;

			var that = thisMapField;
			$('#' + addressField).autocomplete({
				source: function(request, response) {
					// TODO: add 'region' option, to help bias geocoder.
					that.geocoder.geocode( {'address': request.term }, function(results, status) {
						response($.map(results, function(item) {
							return {
								label: item.formatted_address,
								value: item.formatted_address,
								latitude: item.geometry.location.lat(),
								longitude: item.geometry.location.lng()
							};
				 		}));
					});
				},
				 select: function(event, ui) {
				 	that.container.find(".rwmb-map-coordinate").val(ui.item.latitude + ',' + ui.item.longitude );
					var location = new window.google.maps.LatLng(ui.item.latitude, ui.item.longitude);
					that.map.setCenter(location);
						// Drop the Marker
					setTimeout(function(){
						that.marker.setValues({
							position: location,
							animation: window.google.maps.Animation.DROP
						});
					}, 1500);
				 }
			});
		}

		this.bindHandlers = function()
		{
			var that = thisMapField;
			this.container.find('.rwmb-map-goto-address-button').bind('click', function() { that.onFindAddressClick($(this)); });
		}

		this.onFindAddressClick = function($that)
		{
			var $this = $that;
			this.geocodeAddress($this.val());
		}

	}).apply(mapField);

	$(document).ready(function() {
		$('.rwmb-map-field').each(function(){
			mapField.init($(this));
		});
	});

})(jQuery);