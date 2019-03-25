jQuery( function ( $, document ) {
	'use strict';

	$('#rwmb-category-add-toggle').click(function(e){
		e.preventDefault();
		var $this = $( this ).parent();
		$( '.category-add' ).toggleClass('closed');
	});

} );
