var marker,map;
	
jQuery(document).ready(function()
{
	var latlng = new google.maps.LatLng(53.346881,-6.258860);
    map=new google.maps.Map(jQuery(".rwmb-map-canvas")[0],{zoom: 8,center: latlng,mapTypeId: google.maps.MapTypeId.ROADMAP});
	marker=new google.maps.Marker({position: latlng, map: map, title: "xXx", draggable: true});
	geocoder=new google.maps.Geocoder();
	google.maps.event.addListener(map,"click",function(event)
		{
			marker.setPosition(event.latLng);
			updatePositionInput(event.latLng);
		}
	);
	google.maps.event.addListener(marker,"drag",function(event)
		{
			updatePositionInput(event.latLng);
		}
	);
	updatePositionMarker();
});

function updatePositionInput(latLng)
{
	//jQuery('#latitude').val(latLng.lat());
	//jQuery('#longitude').val(latLng.lng());
	jQuery('#rwmb-map-coordinate').val(latLng.lat()+","+latLng.lng());
}

function updatePositionMarker()
{
	//lat=jQuery('#latitude').val();
	//lng=jQuery('#longitude').val();
	coord=jQuery('#rwmb-map-coordinate').val();
	addressField=jQuery('#rwmb-map-goto-address-button').val();
	
	if(coord)
	{
		l=coord.split(",");
		marker.setPosition(new google.maps.LatLng(l[0],l[1]));
		if(l.length>2)
		 zoom=parseInt(l[2]);
		else
		 zoom=15;
		 
		map.setCenter(marker.position);
		map.setZoom(zoom);
	}
	else
	 if(addressField)
	  geocodeAddress(addressField);
}

function geocodeAddress(addressField) 
{
	var address="";
	
	fieldList=addressField.split(",");
	for(loop=0;loop<fieldList.length;loop++)
	 address+=jQuery("#"+fieldList[loop]).val();
	 
	address=address.replace(/\n/g,",");
	address=address.replace(/,,/g,",");
	geocoder.geocode({'address': address},function(results,status)
	{
		if(status==google.maps.GeocoderStatus.OK)
		{
			updatePositionInput(results[0].geometry.location);
			marker.setPosition(results[0].geometry.location);
			map.setCenter(marker.position);
			map.setZoom(15);
		}
	});
}
