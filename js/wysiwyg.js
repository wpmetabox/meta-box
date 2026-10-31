( function( $, wp, window, rwmb ) {
	'use strict';

	/**
	 * Transform textarea into wysiwyg editor.
	 */
	function transform() {
		var $this = $( this ),
			$wrapper = $this.closest( '.wp-editor-wrap' ),
			id = $this.attr( 'id' ),
			isInBlock = $this.closest( '.wp-block, .components-panel' ).length > 0;

		// Update the ID attribute if the editor is in a new block.
		if ( isInBlock ) {
			id = id + '_' + rwmb.uniqid();
			$this.attr( 'id', id );
		}

		// Get current editor mode before updating the DOM.
		var mode = $wrapper.hasClass( 'tmce-active' ) ? 'tmce' : 'html';

		// Update the DOM
		$this.show();
		updateDom( $wrapper, id );

		// Get id of the original editor to get its tinyMCE and quick tags settings
		var originalId = getOriginalId( this ),
			settings = getEditorSettings( originalId ),
			customSettings = $this.closest( '.rwmb-input' ).find( '.rwmb-wysiwyg-id' ).data( 'options' );

		// TinyMCE
		if ( window.tinymce ) {
			settings.tinymce.selector = '#' + id;
			settings.tinymce.setup = function( editor ) {
				editor.on( 'keyup change', function() {
					editor.save(); // Required for live validation.
					$this.trigger( 'change' );
				} );
			};

			// Set editor mode after initializing.
			settings.tinymce.init_instance_callback = function() {
				switchEditors.go( id, mode );
			};

			tinymce.remove( '#' + id );
			tinymce.init( $.extend( settings.tinymce, customSettings.tinymce ) );
		}

		// Quick tags
		if ( window.quicktags ) {
			settings.quicktags.id = id;
			quicktags( $.extend( settings.quicktags, customSettings.quicktags ) );
			QTags._buttonsInit();
		}
	}

	function getEditorSettings( id ) {
		var settings = getDefaultEditorSettings();

		if ( id && tinyMCEPreInit.mceInit.hasOwnProperty( id ) ) {
			settings.tinymce = tinyMCEPreInit.mceInit[ id ];
		}
		if ( id && window.quicktags && tinyMCEPreInit.qtInit.hasOwnProperty( id ) ) {
			settings.quicktags = tinyMCEPreInit.qtInit[ id ];
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
	 * @param el Current cloned textarea
	 */
	function getOriginalId( el ) {
		return el.closest( '.rwmb-input' ).querySelector( '.rwmb-wysiwyg-id' ).dataset.id;
	}

	/**
	 * Update id, class, [data-] attributes, ... of the cloned editor.
	 * @param $wrapper Editor wrapper element
	 * @param id       Editor ID
	 */
	function updateDom( $wrapper, id ) {
		// Wrapper div and media buttons
		$wrapper.attr( 'id', 'wp-' + id + '-wrap' )
			.find( '.mce-container' ).remove().end() // Remove rendered tinyMCE editor
			.find( '.wp-editor-tools' ).attr( 'id', 'wp-' + id + '-editor-tools' )
			.find( '.wp-media-buttons' ).attr( 'id', 'wp-' + id + '-media-buttons' )
			.find( 'button' ).data( 'editor', id ).attr( 'data-editor', id );

		// Set default active mode.
		$wrapper.removeClass( 'html-active tmce-active' );
		$wrapper.addClass( window.tinymce ? 'tmce-active' : 'html-active' );

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

	function init( e ) {
		$( e.target ).find( '.rwmb-wysiwyg' ).each( transform );
	}

	/**
	 * Add required attribute for validation.
	 *
	 * this = textarea element.
	 */
	function addRequiredAttribute() {
		if ( this.classList.contains( 'rwmb-wysiwyg-required' ) ) {
			this.setAttribute( 'required', true );
		}
	}

	/**
	 * Setup events for the classic editor to make live validation work.
	 *
	 * When change:
	 * - Save content to textarea for live validation.
	 * - Trigger change event for compatibility.
	 *
	 * this = textarea element.
	 */
	function setupEvents() {
		if ( !window.tinymce ) {
			return;
		}
		var editor = tinymce.get( this.id );
		if ( !editor ) {
			return;
		}
		var $this = $( this );
		editor.on( 'keyup change', function() {
			editor.save(); // Required for live validation.
			$this.trigger( 'change' );
		} );
	}

	$( function() {
		var $editors = $( '.rwmb-wysiwyg' );
		$editors.each( addRequiredAttribute );
		$editors.each( setupEvents );

		// Force re-render editors in Gutenberg. Use setTimeOut to run after all other code. Bug occurs in WP 5.6.
		if ( rwmb.isGutenberg ) {
			setTimeout( () => $editors.each( transform ), 200 );
		}
	} );

	rwmb.$document
		.on( 'mb_blocks_edit', init )
		.on( 'mb_init_editors', init )
		.on( 'clone', '.rwmb-wysiwyg', function() {
			/*
			 * Transform a textarea to an editor is a heavy task.
			 * Moving it to the end of task queue with setTimeout makes cloning faster.
			 */
			setTimeout( transform.bind( this ), 200 );
		} );
} )( jQuery, wp, window, rwmb );
