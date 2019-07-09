jQuery( function ( $ ) {
	'use strict';

	function setInitialRequiredProp() {
		var $this = $( this ),
			required = $this.prop( 'required' );

		if ( required ) {
			$this.data( 'initial-required', required );
		}
	}

	function unsetRequiredProp() {
		$( this ).prop( 'required', false );
	}

	function setRequiredProp() {
		var $this = $( this );

		if ( $this.data( 'initial-required' ) ) {
			$this.prop( 'required', true );
		}
	}

	function toggleTree() {
		var $this = $( this ),
			val = $this.val(),
			$tree = $this.siblings( '.rwmb-select-tree' ),
			$selected = $tree.filter( "[data-parent-id='" + val + "']" ),
			$notSelected = $tree.not( $selected );

		$selected.removeClass( 'hidden' ).find( 'select' ).each( setRequiredProp );
		$notSelected.addClass( 'hidden' ).find( 'select' ).each( unsetRequiredProp ).prop( 'selectedIndex', 0 );
	}

	function instantiateSelect2() {
		var $this = $( this ),
			options = $this.data( 'options' );

		$this
			.removeClass( 'select2-hidden-accessible' ).removeAttr( 'data-select2-id' )
			.children().removeAttr( 'data-select2-id' ).end()
			.siblings( '.select2-container' ).remove().end()
			.select2( options );

		toggleTree.call( this );
	}

	$( '.rwmb-select-tree > select' ).select2();
	$( '.rwmb-select-tree > select' ).each( setInitialRequiredProp );
	$( '.rwmb-input' )
		.on( 'change', '.rwmb-select-tree > select', toggleTree )
		.on( 'clone', '.rwmb-select-tree > select', instantiateSelect2 );
} );
