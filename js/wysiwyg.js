( function ( $, wp, window, rwmb ) {
	'use strict';

	/**
	 * Transform textarea into wysiwyg editor.
	 */
	function transform() {
		var $this = $( this ),
			$wrapper = $this.closest( '.wp-editor-wrap' ),
			id = $this.attr( 'id' ),
			isInBlock = $this.closest( '.wp-block' ).length > 0;

		// Ignore existing editor.
		if ( ! isInBlock && tinyMCEPreInit.mceInit[id] ) {
			return;
		}

		// Update the ID attribute if the editor is in a new block.
		if ( isInBlock ) {
			id = id + '_' + rwmb.uniqid();
			$this.attr( 'id', id );
		}

		// Update the DOM
		$this.show();
		updateDom( $wrapper, id );

		// Get id of the original editor to get its tinyMCE and quick tags settings
		var originalId = getOriginalId( $this ),
			settings = getEditorSettings( originalId );

		// TinyMCE
		if ( window.tinymce ) {
			var editor = new tinymce.Editor(id, settings.tinymce, tinymce.EditorManager);
			editor.render();

			editor.on( 'keyup change', function() {
				editor.save();
				$this.trigger( 'change' );
			} );
		}

		// Quick tags
		if ( window.quicktags ) {
			settings.quicktags.id = id;
			quicktags( settings.quicktags );
			QTags._buttonsInit();
		}
	}

	function getEditorSettings( id ) {
		var settings = getDefaultEditorSettings();

		if ( id && tinyMCEPreInit.mceInit.hasOwnProperty( id ) ) {
			settings.tinymce = tinyMCEPreInit.mceInit[id];
		}
		if ( id && window.quicktags && tinyMCEPreInit.qtInit.hasOwnProperty( id ) ) {
			settings.quicktags = tinyMCEPreInit.qtInit[id];
		}

		return settings;
	}

	function getDefaultEditorSettings() {
		var settings = wp.editor.getDefaultSettings();

		settings.tinymce.toolbar1 = 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv';
		settings.tinymce.toolbar2 = 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help';

		settings.quicktags.buttons = 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close';

		return settings;
	}

	/**
	 * Get original ID of the textarea
	 * The ID will be used to reference to tinyMCE and quick tags settings
	 * @param $el Current cloned textarea
	 */
	function getOriginalId( $el ) {
		var $clone = $el.closest( '.rwmb-clone' ),
			currentId = $clone.find( '.rwmb-wysiwyg' ).attr( 'id' );

		if ( /_\d+$/.test( currentId ) ) {
			currentId = currentId.replace( /_\d+$/, '' );
		}
		if ( tinyMCEPreInit.mceInit.hasOwnProperty( currentId ) || tinyMCEPreInit.qtInit.hasOwnProperty( currentId ) ) {
			return currentId;
		}

		return '';
	}

	/**
	 * Update id, class, [data-] attributes, ... of the cloned editor.
	 * @param $wrapper Editor wrapper element
	 * @param id       Editor ID
	 */
	function updateDom( $wrapper, id ) {
		// Wrapper div and media buttons
		$wrapper.attr( 'id', 'wp-' + id + '-wrap' )
		        .removeClass( 'html-active' ).addClass( 'tmce-active' ) // Active the visual mode by default
		        .find( '.mce-container' ).remove().end()               // Remove rendered tinyMCE editor
		        .find( '.wp-editor-tools' ).attr( 'id', 'wp-' + id + '-editor-tools' )
		        .find( '.wp-media-buttons' ).attr( 'id', 'wp-' + id + '-media-buttons' )
		        .find( 'button' ).data( 'editor', id ).attr( 'data-editor', id );

		// Editor tabs
		$wrapper.find( '.switch-tmce' )
		        .attr( 'id', id + 'tmce' )
		        .data( 'wp-editor-id', id ).attr( 'data-wp-editor-id', id ).end()
		        .find( '.switch-html' )
		        .attr( 'id', id + 'html' )
		        .data( 'wp-editor-id', id ).attr( 'data-wp-editor-id', id );

		// Quick tags
		$wrapper.find( '.wp-editor-container' ).attr( 'id', 'wp-' + id + '-editor-container' )
		        .find( '.quicktags-toolbar' ).attr( 'id', 'qt_' + id + '_toolbar' ).html( '' );
	}

	/**
	 * Handles updating tiny mce instances when saving a gutenberg post.
	 * https://metabox.io/support/topic/data-are-not-saved-into-the-database/
	 * https://github.com/WordPress/gutenberg/issues/7176
	 */
	function ensureSave() {
		if ( ! wp.data || ! wp.data.hasOwnProperty( 'subscribe' ) || ! window.tinyMCE ) {
			return;
		}
		wp.data.subscribe( function() {
			var editor = wp.data.hasOwnProperty( 'select' ) ? wp.data.select( 'core/editor' ) : {};

			if ( editor && editor.isSavingPost && editor.isSavingPost() ) {
				window.tinyMCE.triggerSave();
			}
		} );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-wysiwyg' ).each( transform );
	}

	ensureSave();
	rwmb.$document
		.on( 'mb_blocks_edit', init )
		.on( 'clone', '.rwmb-wysiwyg', transform );
} )( jQuery, wp, window, rwmb );
