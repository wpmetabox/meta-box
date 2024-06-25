( $ => {
	'use strict';
    
    const $body = $( 'body' );

	const defaultOptions = {
		wrapper: `<div class="rwmb-modal">
			<header class="rwmb-modal-title">
				<h2></h2>
				<button type="button" class="rwmb-modal-close">&times;</button>
			</header>
			<div class="rwmb-modal-content"></div>
		</div>`,
		markupIframe: '<iframe id="rwmb-modal-iframe" width="100%" height="700" src="{URL}" border="0"></iframe>',
        markupOverlay: '<div class="rwmb-modal-overlay"></div>',
		hideElement: '',
		hideElementDefault: '#adminmenumain, #wpadminbar, #wpfooter, .row-actions, .form-wrap.edit-term-notes, #screen-meta-links, .wp-heading-inline, .wp-header-end, .page-title-action',
		callback: null,
		closeModalCallback: null,
		isBlockEditor: false,
		$objectId: null,
		$objectDisplay: null,
        isEdit: false,
        size: 'large',
	};

	$.fn.rwmbModal = function ( options = {} ) {
		options = {
			...defaultOptions,
			...options
		};

		if ( $( '.rwmb-modal' ).length === 0 ) {
			return;
		}

		// $this is the button that opens the modal
		const $this = $( this ),
			$modal = $( '.rwmb-modal' );

		let $input = $this.closest( '.rwmb-input' );
		if ( $input.find( '.rwmb-clone' ).length > 0 && $this.closest( '.rwmb-clone' ).length > 0 ) {
			$input = $this.closest( '.rwmb-clone' );
		}

		$this.click( function ( e ) {
			e.preventDefault();
            $modal.attr( 'size', options.size );
			$modal.find( '.rwmb-modal-title h2' ).html( $this.html() );
			$modal.find( '.rwmb-modal-content' ).html( options.markupIframe.replace( '{URL}', $this.attr( 'data-url' ) ) );

			$( '#rwmb-modal-iframe' ).on( 'load', function () {
				const $contents = $( this ).contents();
				options.isBlockEditor = $contents.find( 'body' ).hasClass( 'block-editor-page' );

				$contents.find( options.hideElement ).hide();

				$modal.find( '.rwmb-modal-title' ).css( 'background-color', '' );
				if ( options.isBlockEditor ) {
					$modal.find( '.rwmb-modal-title' ).css( 'background-color', '#fff' );
				}

				$contents
					.find( options.hideElementDefault ).hide().end()
					.find( '.rwmb-modal-add-button' ).parents('.rwmb-field').hide();
				$contents.find( 'html' ).css( 'padding-top', 0 ).end()
					.find( '#wpcontent' ).css( 'margin-left', 0 ).end()
					.find( 'a' ).on( 'click', e => e.preventDefault() );

				if ( options.callback !== null && typeof options.callback === 'function' ) {
					options.callback( $modal, $contents );
				}

                $body.addClass( 'rwmb-modal-show' );
				$( '.rwmb-modal-overlay' ).fadeIn( 'medium' ).css( 'display', 'flex' );
				$modal.fadeIn( 'medium' ).css( 'display', 'flex' );

				return false;
			} );

			$( '.rwmb-modal-close' ).on( 'click', function ( event ) {
				if ( options.closeModalCallback !== null && typeof options.closeModalCallback === 'function' ) {
					options.closeModalCallback( $( '#rwmb-modal-iframe' ).contents(), $input );
				}

                $modal.fadeOut( 'medium' );
				$( '.rwmb-modal-overlay' ).fadeOut( 'medium' );
				$body.removeClass( 'rwmb-modal-show' );

				// If not add new
				if ( !options.$objectId || !options.$objectDisplay ) {
					$( this ).off( event );
					return;
				}

				// Select, select advanced, select tree.
				const $select = $input.find( 'select' );
				if ( $select.length > 0 ) {
					$select.prepend( $( '<option>', {
						value: options.$objectId,
						text: options.$objectDisplay,
						selected: true
					} ) );

					$( this ).off( event );
					return;
				}

				// Radio, checkbox list, checkbox tree
				const $inputList = $input.find( '.rwmb-input-list:first' ),
					$labelClone = $inputList.find( '> label:first' ).clone(),
					$inputClone = $labelClone.find( 'input' ).clone();

				$labelClone.html(
					$inputClone.val( options.$objectId )
						.attr( 'checked', true )
						.prop( 'outerHTML' ) + options.$objectDisplay
				);
				$inputList.prepend( $labelClone );

				// Clear event after close modal.
				options.$objectId = null;
				options.$objectDisplay = null;
				$( this ).off( event );
			});
		} );
	};

	if ( $( '.rwmb-modal' ).length === 0 ) {
		$body.append( defaultOptions.wrapper )
			.append( defaultOptions.markupOverlay );
	}

} )( jQuery );