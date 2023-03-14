( function( $, rwmb ) {
	'use strict';

	// Cache ajax requests: https://github.com/select2/select2/issues/110#issuecomment-419247158
	const cache = {};

	function transform( options ) {

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
			console.log( data );

			return $.ajax( {
				url: options.ajax.url,
				type: 'post',
				dataType: 'json',
				data,
				success: function ( res ) { console.log(res) }
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
		callback: null
	};

	$.fn.rwmbModal = function( options = {} ) {
		options = {
			...defaultOptions,
			...options
		};

		if ( $( '.rwmb-modal' ).length === 0 ) {
			return;
		}

		const $this = $( this ),
			$modal = $( '.rwmb-modal' ),
			$input = $this.closest( '.rwmb-input' );

		$this.on( 'click', function ( e ) {
			e.preventDefault();
			
			$modal.find( '.rwmb-modal-title h2' ).html( $this.html() );
			$modal.find( '.rwmb-modal-content' ).html( options.markupIframe.replace( '{URL}', $this.data( 'url' ) ) );
			$( '#rwmb-modal-iframe' ).on( 'load', function() {
				const $contents = $( this ).contents();
				$contents.find( options.removeElementDefault ).remove();
				if ( options.removeElement !== '' ) {
					$contents.find( options.removeElement ).remove();
				}
				$contents.find( '.rwmb-modal-add-button' ).parent().remove();

				$modal.find( '.rwmb-modal-title' ).css( 'background-color', '' );
				if ( $contents.find( 'html.interface-interface-skeleton__html-container' ).length > 0 ) {
					$modal.find( '.rwmb-modal-title' ).css( 'background-color', '#ffffff' );
				}

				$contents.find( 'a' ).on( 'click', function( e ) {
					e.preventDefault();
					return false;
				} );

				if ( options.callback !== null && typeof options.callback === 'function' ) {
					options.callback( $contents );
				}

				$body.addClass( 'rwmb-modal-show' );
				$( '.rwmb-modal-overlay' ).fadeIn( 'medium' );
				$modal.fadeIn( 'medium' );
			} );

			$( '.rwmb-modal-close' ).on( 'click', function() {
				$modal.fadeOut( 'medium' );
				$( '.rwmb-modal-overlay' ).fadeOut( 'medium' );
				$body.removeClass( 'rwmb-modal-show' );
				// $input.find( '> *[data-options]' ).rwmbTransform();
				if ( $input.find( '> *[data-options]' ).length > 1 ) {
					$input.find( '> *[data-options]:first' ).rwmbTransform( );
				} else {
					if ( $input.find( '.rwmb-select-tree' ).length > 0 ) {
						$input.find( '*[data-options]:first' ).rwmbTransform( 'select-tree' );
					} else {
						transform( $input.find( '> *[data-options]' ).data( 'options' ) )	
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