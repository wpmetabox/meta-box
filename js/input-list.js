( function ( $, rwmb ) {
	'use strict';

	function transformSuccess( event, data ) {
		// No select
		if ( $( this ).find( '.rwmb-input-list' ).length === 0 ) {
			return true;
		}

		const $checkboxList = $( this ).find( '.rwmb-input-list' );
		const $checkboxClone = $checkboxList.find( 'label:first input' ).clone();
		const $selected = $checkboxList.find( 'input:checked' ).val();

		$checkboxList.find( 'li' ).remove();
		// No data		
		if ( data.items.length === 0 ) {
			return;
		}

		$.each( data.items, function ( index, option ) {
			$checkboxList.append( $( '<li>' ).html( $( '<label>' ).html(
				$checkboxClone.val( option.value )
					.attr( 'checked', Boolean( option.value == $selected ) )
					.prop( 'outerHTML' ) +
				( typeof option.label === 'object' ? option.label.nickname[ 0 ] : option.label )
			) ) );
		} );
	};

	function toggleTree() {
		var $this = $( this ),
			$children = $this.closest( 'li' ).children( 'ul' );

		if ( $this.is( ':checked' ) ) {
			$children.removeClass( 'hidden' );
		} else {
			$children.addClass( 'hidden' ).find( 'input' ).prop( 'checked', false );
		}
	}

	function toggleAll( e ) {
		e.preventDefault();

		var $this = $( this ),
			checked = $this.data( 'checked' );

		if ( undefined === checked ) {
			checked = true;
		}

		$this.parent().siblings( '.rwmb-input-list' ).find( 'input' ).prop( 'checked', checked ).trigger( 'change' );

		checked = !checked;
		$this.data( 'checked', checked );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-input-list.rwmb-collapse input[type="checkbox"]' ).each( toggleTree );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'change', '.rwmb-input-list.rwmb-collapse input[type="checkbox"]', toggleTree )
		.on( 'clone', '.rwmb-input-list.rwmb-collapse input[type="checkbox"]', toggleTree )
		.on( 'click', '.rwmb-input-list-select-all-none', toggleAll )
		.on( 'transformSuccess', '.rwmb-input', transformSuccess );;
} )( jQuery, rwmb );
