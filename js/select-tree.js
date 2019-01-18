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

	function update() {
		var $this = $( this ),
			val = $this.val(),
			$selected = $this.siblings( "[data-parent-id='" + val + "']" ),
			$notSelected = $this.siblings().not( $selected ),
			options = $this.data( 'options' );

		// Turn select into beautiful select2.
		$this.removeClass( 'select2-hidden-accessible' );
		$this.siblings( '.select2-container' ).remove();
		$this.show().select2( options );

		$selected.removeClass( 'hidden' ).find( 'select' ).each( setRequiredProp );
		$notSelected.addClass( 'hidden' ).find( 'select' ).each( unsetRequiredProp ).prop( 'selectedIndex', 0 );
	}

	$( '.rwmb-select-tree select' ).select2();
	$( '.rwmb-select-tree select' ).each( setInitialRequiredProp );
	$( '.rwmb-input' )
		.on( 'change', '.rwmb-select-tree select', update )
		.on( 'clone', '.rwmb-select-tree select', update );
} );
