
var marker, map, geocoder;

jQuery( document ).ready( function ()
{
   /*jshint jquery: true, devel:true, browser: true, newcap: true, noempty: true, strict: true, undef: true */
   "use strict";
   var latlng = new window.google.maps.LatLng( 53.346881, -6.258860 );
   map        = new window.google.maps.Map( jQuery( '.rwmb-map-canvas' )[0], {
      zoom              : 8,
      center            : latlng,
      streetViewControl : 0,
      mapTypeId         : window.google.maps.MapTypeId.ROADMAP
      });
   marker     = new window.google.maps.Marker( {position: latlng, map: map, draggable: true} );
   geocoder   = new window.google.maps.Geocoder();

   window.google.maps.event.addListener( map, 'click', function ( event )
   {
      marker.setPosition( event.latLng );
      updatePositionInput( event.latLng );
   } );
   window.google.maps.event.addListener( marker, 'drag', function ( event )
   {
      updatePositionInput( event.latLng );
   } );
   updatePositionMarker();

   autoCompleteAddress();

   function updatePositionInput( latLng )
   {
      jQuery( '#rwmb-map-coordinate' ).val( latLng.lat() + ',' + latLng.lng() );
   }

   function updatePositionMarker()
   {
      var coord = jQuery( '#rwmb-map-coordinate' ).val(),
         addressField = jQuery( '#rwmb-map-goto-address-button' ).val(),
         l, zoom;

      if ( coord )
      {
         l = coord.split( ',' );
         marker.setPosition( new window.google.maps.LatLng( l[0], l[1] ) );

         zoom = l.length > 2 ? parseInt( l[2], 10 ) : 15;

         map.setCenter( marker.position );
         map.setZoom( zoom );
      }
      else
         if ( addressField ){
            geocodeAddress( addressField );
         }
   }

   function geocodeAddress( addressField )
   {
      console.log(addressField);
      var address = '',
         fieldList = addressField.split( ',' ),
         loop;

      for ( loop = 0; loop < fieldList.length; loop++ )
      {
         address += jQuery( '#' + fieldList[loop] ).val();
      }

      address = address.replace( /\n/g, ',' );
      address = address.replace( /,,/g, ',' );
      geocoder.geocode( {'address': address}, function ( results, status )
      {
         if ( status == window.google.maps.GeocoderStatus.OK )
         {
            updatePositionInput( results[0].geometry.location );
            marker.setPosition( results[0].geometry.location );
            map.setCenter( marker.position );
            map.setZoom( 15 );
         }
      } );
   }


   function autoCompleteAddress(){
      var addressField = jQuery( '#rwmb-map-goto-address-button' ).val();
      if (!addressField) return null;

      jQuery( '#' + addressField).autocomplete({
         source: function(request, response) {
            // TODO: add 'region' option, to help bias geocoder.
           geocoder.geocode( {'address': request.term }, function(results, status) {
             response(jQuery.map(results, function(item) {
               return {
                 label     : item.formatted_address,
                 value     : item.formatted_address,
                 latitude  : item.geometry.location.lat(),
                 longitude : item.geometry.location.lng()
               };
             }));
           });
         },
         select: function(event, ui) {
                 
         jQuery("#rwmb-map-coordinate").val(ui.item.latitude + ',' + ui.item.longitude );       
         
           var location = new window.google.maps.LatLng(ui.item.latitude, ui.item.longitude);

           map.setCenter(location);
            // Drop the Marker
           setTimeout( function(){
              marker.setValues({
                 position    : location,
                 animation   : window.google.maps.Animation.DROP
              });
           }, 1500);
         }
      });
   }
} );
