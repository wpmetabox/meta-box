( function ( $, rwmb ) {
	'use strict';

	function transformSuccess( event, data ) {
		// No select
		if ( $( this ).find( '.rwmb-input-list' ).length === 0 ) {
			return true;
		}

		const $checkboxList = $( this ).find( '.rwmb-input-list' );
		const $checkboxClone = $checkboxList.find( '> *:first' ).clone();
		const $inputClone = $checkboxClone.find( 'input' ).clone();
		let $selected = $checkboxList.find( 'input:checked' ).length === 0 ?
			[ $checkboxList.find( 'input:checked' ).val() ] :
			[ ...$( 'input:checked' ).map( ( k, v ) => parseInt( v.value ) ) ];
		
		if ( $checkboxList.attr( 'data-selected' ) ) {
			$selected = ( $inputClone.attr( 'type' ) === 'radio' ) ? [ parseInt( $checkboxList.attr( 'data-selected' ) ) ] :
				( $selected ? [ ...$selected, parseInt( $checkboxList.attr( 'data-selected' ) ) ] : [ parseInt( $checkboxList.attr( 'data-selected' ) ) ] );
		}
		
		$checkboxList.empty();

		// No data		
		if ( data.items.length === 0 ) {
			return;
		}

		$.each( data.items, function ( index, option ) {
			$checkboxClone.find( 'input' ).parent().empty().html(
				$inputClone.val( option.value )
					.attr( 'checked', $selected.includes( option.value ) )
					.prop( 'outerHTML' ) + option.label
			);

			$checkboxList.append( $checkboxClone.prop( 'outerHTML' ) );
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
