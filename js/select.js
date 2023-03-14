( function ( $, rwmb ) {
	'use strict';

	$.fn.transformSuccess = function ( data ) {
		// No select
		if ( $( this ).find( '.rwmb-select' ).length === 0 ) {
			return;
		}

		const $select = $( this ).find( '.rwmb-select' );
		$select.find( 'option[value!=""]' ).remove();
		// No data		
		if ( data.items.length === 0 ) {
			return;
		}

		$.each( data.items, function ( index, option ) {
			$select.append( $( '<option>' ).val( option.value ).text( option.label ) );
		} );
	};		

	function toggleAll( e ) {
		e.preventDefault();

		var $this = $( this ),
			$select = $this.parent().siblings( 'select' );

		if ( 'none' === $this.data( 'type' ) ) {
			$select.val( [] ).trigger( 'change' );
			return;
		}
		var selected = [];
		$select.find( 'option' ).each( function ( index, option ) {
			selected.push( option.value );
		} );
		$select.val( selected ).trigger( 'change' );
	};

	rwmb.$document.on( 'click', '.rwmb-select-all-none a', toggleAll );
} )( jQuery, rwmb );
