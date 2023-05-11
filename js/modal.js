( function ( $, rwmb ) {
	'use strict';

	// Cache ajax requests: https://github.com/select2/select2/issues/110#issuecomment-419247158
	const cache = {};

	function transform( $input, options ) {

		if ( options.ajax_data ) {
			var actions = {
				'post': 'rwmb_get_posts',
				'taxonomy': 'rwmb_get_terms',
				'taxonomy_advanced': 'rwmb_get_terms',
				'user': 'rwmb_get_users'
			};
			const data = {
				...options.ajax_data,
				action: actions[ options.ajax_data.field.type ]
			};

			return $.ajax( {
				url: options.ajax.url,
				type: 'post',
				dataType: 'json',
				data,
				success: function ( res ) {
					if ( res.success === true ) {
						$input.trigger( 'transformSuccess', [ res.data ] );
					}
				}
			} );
		}
	}

	const $body = $( 'body' );

	const defaultOptions = {
		wrapper: `<div class="rwmb-modal">
			<div class="rwmb-modal-title">
				<h2></h2>
				<button type="button" class="rwmb-modal-close">&times;</button>
			</div>
			<div class="rwmb-modal-content"></div>
		</div>`,
		markupIframe: '<iframe id="rwmb-modal-iframe" width="100%" height="700" src="{URL}" border="0"></iframe>',
		markupOverlay: '<div class="rwmb-modal-overlay"></div>',
		removeElement: '',
		removeElementDefault: '#adminmenumain, #wpadminbar, #wpfooter, .row-actions, .form-wrap.edit-term-notes, #screen-meta-links, .wp-heading-inline, .wp-header-end',
		callback: null,
		closeModalCallback: null,
		isBlockEditor: false
	};

	$.fn.rwmbModal = function ( options = {} ) {
		options = {
			...defaultOptions,
			...options
		};

		if ( $( '.rwmb-modal' ).length === 0 ) {
			return;
		}

		const $this = $( this ),
			$modal = $( '.rwmb-modal' ),
			$input = $this.closest( '.rwmb-clone' ).length > 0 ? $this.closest( '.rwmb-clone' ) : $this.closest( '.rwmb-input' );

		$this.closest( '.rwmb-input' ).on( 'click', '.rwmb-modal-add-button', function ( e ) {
			e.preventDefault();

			$modal.find( '.rwmb-modal-title h2' ).html( $this.html() );
			$modal.find( '.rwmb-modal-content' ).html( options.markupIframe.replace( '{URL}', $this.data( 'url' ) ) );
			$( '#rwmb-modal-iframe' ).on( 'load', function () {
				const $contents = $( this ).contents();
				options.isBlockEditor = $contents.find( 'body' ).hasClass( 'block-editor-page' );

				if ( options.removeElement !== '' ) {
					$contents.find( options.removeElement ).remove();
				}

				$modal.find( '.rwmb-modal-title' ).css( 'background-color', '' );
				if ( options.isBlockEditor ) {
					$modal.find( '.rwmb-modal-title' ).css( 'background-color', '#fff' );
				}

				$contents
					.find( options.removeElementDefault ).remove().end()
					.find( '.rwmb-modal-add-button' ).parent().remove();
				$contents.find( 'html' ).css( 'padding-top', 0 ).end()
					.find( '#wpcontent' ).css( 'margin-left', 0 ).end()
					.find( 'a' ).on( 'click', e => e.preventDefault() );

				if ( options.callback !== null && typeof options.callback === 'function' ) {
					options.callback( $contents );
				}

				$body.addClass( 'rwmb-modal-show' );
				$( '.rwmb-modal-overlay' ).fadeIn( 'medium' );
				$modal.fadeIn( 'medium' );
			} );

			$( '.rwmb-modal-close' ).on( 'click', function () {

				if ( options.closeModalCallback !== null && typeof options.closeModalCallback === 'function' ) {
					options.closeModalCallback( $( '#rwmb-modal-iframe' ).contents(), $input );
				}

				$modal.fadeOut( 'medium' );
				$( '.rwmb-modal-overlay' ).fadeOut( 'medium' );
				$body.removeClass( 'rwmb-modal-show' );
				// $input.find( '> *[data-options]' ).rwmbTransform();
				if ( $input.find( '> *[data-options]' ).length > 1 ) {
					$input.find( '> *[data-options]:first' ).rwmbTransform();
				} else {
					if ( $input.find( '.rwmb-select-tree' ).length > 0 ) {
						$input.find( '*[data-options]:first' ).rwmbTransform( 'select-tree' );
					} else {
						transform( $input, $input.find( '> *[data-options]' ).data( 'options' ) );
					}
				}
			} );
		} );
	};

	if ( $( '.rwmb-modal' ).length === 0 ) {
		$body.append( defaultOptions.wrapper )
			.append( defaultOptions.markupOverlay );
	}
} )( jQuery, rwmb );