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
			$clone = $this.siblings( '.rwmb-file-input:last' ).clone();

		$clone.insertBefore( this );
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
			$uploaded = $this.closest( '.rwmb-uploaded' ),
			data = {
				action: 'rwmb_delete_file',
				_ajax_nonce: $uploaded.data( 'delete_nonce' ),
				post_id: $( '#post_ID' ).val(),
				field_id: $uploaded.data( 'field_id' ),
				attachment_id: $this.data( 'attachment_id' ),
				force_delete: $uploaded.data( 'force_delete' )
			};

		$item.remove();
		file.updateVisibility.call( $uploaded );

		$.post( ajaxurl, data, function ( response ) {
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
		var $this = $( this ),
			data = {
				action: 'rwmb_reorder_files',
				_ajax_nonce: $this.data( 'reorder_nonce' ),
				post_id: $( '#post_ID' ).val(),
				field_id: $this.data( 'field_id' )
			};
		$this.sortable( {
			placeholder: 'ui-state-highlight',
			items: 'li',
			update: function () {
				data.order = $this.sortable( 'serialize' );
				$.post( ajaxurl, data );
			}
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

	// Initialize when document ready.
	$( function ( $ ) {
		$( document )
			.on( 'click', '.rwmb-add-file', file.addHandler )
			.on( 'click', '.rwmb-delete-file', file.deleteHandler );

		var $uploaded = $( '.rwmb-uploaded' );
		$uploaded.each( file.sort );
		$uploaded.each( file.updateVisibility );
	} );
} )( jQuery, document );
