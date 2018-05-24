jQuery( function ( $ ) {
	'use strict';

	/**
	 * Reorder selected values in correct order that they were selected.
	 * @param $select2 jQuery element of the select2.
	 */
	function reorderSelected( $select2 ) {
		var selected = $select2.data( 'selected' );
		if ( ! selected ) {
			return;
		}
		selected.forEach( function ( value ) {
			var option = $select2.children( '[value="' + value + '"]' );
			option.detach();
			$select2.append( option );
		} );
		$select2.trigger( 'change' );
	}

	/**
	 * Turn select field into beautiful dropdown with select2 library
	 * This function is called when document ready and when clone button is clicked (to update the new cloned field)
	 *
	 * @return void
	 */
	function update() {
		var $this = $( this ),
			options = $this.data( 'options' );
		$this.removeClass( 'select2-hidden-accessible' );
		$this.siblings( '.select2-container' ).remove();
		$this.show().select2( options );

		rwmbSelect.bindEvents( $this );

		if ( ! $this.attr( 'multiple' ) ) {
			return;
		}

		reorderSelected( $this );

		/**
		 * Preserve the order that options are selected.
		 * @see https://github.com/select2/select2/issues/3106#issuecomment-255492815
		 */
		$this.on( 'select2:select', function ( event ) {
			var option = $this.children( '[value="' + event.params.data.id + '"]' );
			option.detach();
			$this.append( option ).trigger( 'change' );
		} );
	}

	$( '.rwmb-select_advanced' ).each( update );
	$( document ).on( 'clone', '.rwmb-select_advanced', update );
} );
