window.rwmb = window.rwmb || {};
window.rwmb.editor = window.rwmb.editor || {};
jQuery( function ( $ ) {
	/**
	 * Initialize TinyMCE and/or Quicktags. For use with wp_enqueue_editor() (PHP).
	 *
	 * Intended for use with an existing textarea that will become the Text editor tab.
	 * The editor width will be the width of the textarea container, height will be adjustable.
	 *
	 * Settings for both TinyMCE and Quicktags can be passed on initialization, and are "filtered"
	 * with custom jQuery events on the document element, wp-before-tinymce-init and wp-before-quicktags-init.
	 *
	 * @since 4.8
	 *
	 * @param {string} id The HTML id of the textarea that is used for the editor.
	 *                    Has to be jQuery compliant. No brackets, special chars, etc.
	 * @param {object} settings Example:
	 * settings = {
	 *    // See https://www.tinymce.com/docs/configure/integration-and-setup/.
	 *    // Alternatively set to `true` to use the defaults.
	 *    tinymce: {
	 *        setup: function( editor ) {
	 *            console.log( 'Editor initialized', editor );
	 *        }
	 *    }
	 *
	 *    // Alternatively set to `true` to use the defaults.
	 *	  quicktags: {
	 *        buttons: 'strong,em,link'
	 *    }
	 * }
	 */

	rwmb.editor.initialize = function( id, settings ) {
		var init;
		var defaults;

		if ( ! $ || ! id || ! wp.editor.getDefaultSettings ) {
			return;
		}

		defaults = wp.editor.getDefaultSettings();

		// Initialize TinyMCE by default
		if ( ! settings ) {
			settings = {
				tinymce: true
			};
		}

		// Add wrap and the Visual|Text tabs.
		if ( settings.tinymce && settings.quicktags ) {
			var $textarea = $( '#' + id );
			var $wrap = $( '<div>' ).attr( {
					'class': 'wp-core-ui wp-editor-wrap tmce-active',
					id: 'wp-' + id + '-wrap'
				} );
			var $editorContainer = $( '<div class="wp-editor-container">' );
			var $button = $( '<button>' ).attr( {
					type: 'button',
					'data-wp-editor-id': id
				} );
			var $media = '';
			if ( settings.media ){
				$media = $( '<div>' ).attr( {
					id: 'wp-' + id + '-media-buttons',
					class: 'wp-media-buttons'
				})
					.append( $( '<button>' ).attr( {
						class: 'button insert-media add_media',
						'data-editor': id } )
						.append( $('<span>').attr( {
							class: 'wp-media-buttons-icon'
						}), window.tinymce.translate( 'Add Media' ))
					);
			}
			$wrap.append(
				$( '<div class="wp-editor-tools">' )
					.append($media)
					.append( $( '<div class="wp-editor-tabs">' )
						.append( $button.clone().attr({
							id: id + '-tmce',
							'class': 'wp-switch-editor switch-tmce'
						}).text( window.tinymce.translate( 'Visual' ) ) )
						.append( $button.attr({
							id: id + '-html',
							'class': 'wp-switch-editor switch-html'
						}).text( window.tinymce.translate( 'Text' ) ) )
					).append( $editorContainer )
			);

			$textarea.after( $wrap );
			$editorContainer.append( $textarea );
		}

		if ( window.tinymce && settings.tinymce ) {
			if ( typeof settings.tinymce !== 'object' ) {
				settings.tinymce = {};
			}

			init = $.extend( {}, defaults.tinymce, settings.tinymce );
			init.selector = '#' + id;

			$( document ).trigger( 'wp-before-tinymce-init', init );
			window.tinymce.init( init );

			if ( ! window.wpActiveEditor ) {
				window.wpActiveEditor = id;
			}
		}

		if ( window.quicktags && settings.quicktags ) {
			if ( typeof settings.quicktags !== 'object' ) {
				settings.quicktags = {};
			}

			init = $.extend( {}, defaults.quicktags, settings.quicktags );
			init.id = id;

			$( document ).trigger( 'wp-before-quicktags-init', init );
			window.quicktags( init );

			if ( ! window.wpActiveEditor ) {
				window.wpActiveEditor = init.id;
			}
		}
	};

	function update() {
		var $this = $( this ),
			options = $this.data(),
			id = $this.get(0).id;
			console.log($this);
		$this.show().closest( '.wp-editor-wrap' ).replaceWith($this);
		rwmb.editor.initialize( id, options );
	}
	$( ':input.rwmb-editor' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-editor', update );

} );
