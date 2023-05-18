( function ( $, rwmb ) {
	'use strict';

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
		isBlockEditor: false,
		$objectId: null,
		$objectDisplay: null
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
			$modal = $( '.rwmb-modal' );

		let $input = $this.closest( '.rwmb-input' );
		if ( $input.find( '.rwmb-clone' ).length > 0 && $this.closest( '.rwmb-clone' ).length > 0 ) {
			$input = $this.closest( '.rwmb-clone' );
		}

		$this.click( function ( e ) {
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

				return false;
			} );

			$( '.rwmb-modal-close' ).on( 'click', function ( event ) {
				console.log( event );
				if ( options.closeModalCallback !== null && typeof options.closeModalCallback === 'function' ) {
					options.closeModalCallback( $( '#rwmb-modal-iframe' ).contents(), $input );
				}

				$modal.fadeOut( 'medium' );
				$( '.rwmb-modal-overlay' ).fadeOut( 'medium' );
				$body.removeClass( 'rwmb-modal-show' );

				// If not add new
				if ( !options.$objectId || !options.$objectDisplay || options.$objectDisplay === '' ) {
					$( this ).off( event );
					return;
				}

				// Select advanced: 2 data-options - one is the select advanced, one is the <a> tag.
				if ( $input.find( '> *[data-options]' ).length > 1 ) {
					$input.find( 'select' ).attr( 'data-selected', options.$objectId );
					$input.find( 'select :selected' ).removeAttr( 'selected' );

					$input.find( 'select' ).prepend( $( '<option>', {
						value: options.$objectId,
						text: options.$objectDisplay,
						selected: true
					} ) );

					$input.find( '> *[data-options]:first' ).rwmbTransform();

					$( this ).off( event );
					return;
				}

				// Select tree
				if ( $input.find( '.rwmb-select-tree' ).length > 0 ) {
					$input.find( '.rwmb-select-tree' ).find( 'select' ).attr( 'data-selected', options.$objectId );
					$input.find( '.rwmb-select-tree' ).find( 'select :selected' ).removeAttr( 'selected' );

					$input.find( '.rwmb-select-tree' ).find( 'select' ).prepend( $( '<option>', {
						value: options.$objectId,
						text: options.$objectDisplay,
						selected: true
					} ) );

					$input.find( '*[data-options]:first' ).rwmbTransform( 'select-tree' );

					$( this ).off( event );
					return;
				}

				//Select
				if ( $input.find( '.rwmb-select' ).length > 0 ) {
					$input.find( '.rwmb-select' ).find( 'select' ).attr( 'data-selected', options.$objectId );
					$input.find( 'select' ).prepend( $( '<option>', {
						value: options.$objectId,
						text: options.$objectDisplay,
						selected: true
					} ) );

					$( this ).off( event );
					return;
				}

				//radio list, checkbox list, checkbox tree
				$input.find( '.rwmb-input-list' ).attr( 'data-selected', options.$objectId );

				const $inputListClone = $input.find( '.rwmb-input-list' ).find( '> *:first' ).clone();
				const $inputClone = $inputListClone.find( 'input' ).clone();

				$inputListClone.find( 'input' ).parent().empty().html(
					$inputClone.val( options.$objectId )
						.attr( 'checked', true )
						.prop( 'outerHTML' ) + options.$objectDisplay
				);

				$input.find( '.rwmb-input-list' ).prepend( $inputListClone.prop( 'outerHTML' ) );

				// Clear event after close modal.
				$( this ).off( event );
			} );
		} );
	};

	if ( $( '.rwmb-modal' ).length === 0 ) {
		$body.append( defaultOptions.wrapper )
			.append( defaultOptions.markupOverlay );
	}

} )( jQuery, rwmb );