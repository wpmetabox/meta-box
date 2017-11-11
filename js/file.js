/* global jQuery */
( function ( $, document ) {
	'use strict';

	var file = {};

	/**
	 * Handles a click on add new file.
	 * Expects `this` to equal the clicked element.
	 *
	 * @param event Click event.
	 */
	file.addHandler = function ( event ) {
		event.preventDefault();

		var $this = $( this ),
			$clone = $this.prev().clone();

		$clone.insertBefore( this ).find( 'input' ).val( '' );

		file.updateVisibility.call( $this.closest( '.rwmb-input' ).find( '.rwmb-uploaded' )[0] );
	};

	/**
	 * Handles a click on delete new file.
	 * Expects `this` to equal the clicked element.
	 *
	 * @param event Click event.
	 */
	file.deleteHandler = function ( event ) {
		event.preventDefault();

		var $this = $( this ),
			$item = $this.closest( 'li' ),
			$uploaded = $this.closest( '.rwmb-uploaded' );

		$item.remove();
		file.updateVisibility.call( $uploaded );

		if ( 1 > $uploaded.data( 'force_delete' ) ) {
			return;
		}

		$.post( ajaxurl, {
			action: 'rwmb_delete_file',
			_ajax_nonce: $uploaded.data( 'delete_nonce' ),
			field_id: $uploaded.data( 'field_id' ),
			attachment_id: $this.data( 'attachment_id' )
		}, function ( response ) {
			if ( ! response.success ) {
				alert( response.data );
			}
		}, 'json' );
	};

	/**
	 * Sort uploaded files.
	 * Expects `this` to equal the uploaded file list.
	 */
	file.sort = function () {
		$( this ).sortable( {
			placeholder: 'ui-state-highlight',
			items: 'li'
		} );
	};

	/**
	 * Update visibility of upload inputs and Add new file link.
	 * Expect this equal to the uploaded file list.
	 */
	file.updateVisibility = function () {
		var $uploaded = $( this ),
			max = parseInt( $uploaded.data( 'max_file_uploads' ), 10 ),
			$uploader = $uploaded.siblings( '.rwmb-new-files' ),
			$addMore = $uploader.find( '.rwmb-add-file' ),
			numFiles = $uploaded.children().length,
			numInputs = $uploader.find( '.rwmb-file-input' ).length;

		$uploaded.toggle( 0 < numFiles );
		if ( 0 === max ) {
			return;
		}
		$uploader.toggle( numFiles < max );
		$addMore.toggle( numFiles + numInputs < max );
	};

	// Reset field when cloning.
	file.resetClone = function() {
		var $this = $( this ),
			$clone = $this.closest( '.rwmb-clone' );
		$clone.find( '.rwmb-uploaded' ).remove();
		$clone.find( '.rwmb-file-input' ).not( ':first' ).remove();
	};

	// Initialize when document ready.
	$( function ( $ ) {
		$( document )
			.on( 'click', '.rwmb-add-file', file.addHandler )
			.on( 'click', '.rwmb-delete-file', file.deleteHandler )
			.on( 'clone', '.rwmb-file-input input', file.resetClone );

		var $uploaded = $( '.rwmb-uploaded' );
		$uploaded.each( file.sort );
		$uploaded.each( file.updateVisibility );
	} );
} )( jQuery, document );
