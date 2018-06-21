( function( $ ) {
    "use strict";
    
    var maps = document.querySelectorAll( '.rwmb-osm-map-canvas' );
    maps.forEach( function( mapEl ) {
        mapEl.id = 'rwmb-osm-map-' + Date.now();
        var inputVal = mapEl.nextElementSibling;
        console.log( inputVal );
        var latLng = inputVal.value.split( ',' ).map( parseFloat );
        var map = L.map( mapEl.id ).setView( latLng, 13 );
        var marker = L.marker( latLng, { draggable: true } ).addTo( map );
        marker.on( 'dragend', function ( e ) {
            var position = marker.getLatLng();
            marker.setLatLng( new L.LatLng( position.lat, position.lng ), { draggable: true } );
            map.panTo( new L.LatLng( position.lat, position.lng ) );
            inputVal.value = [ position.lat, position.lng ].join( ',' );
            console.log( inputVal, position );
        });

    });
})( jQuery );