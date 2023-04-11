( function ( $, rwmb ) {
	'use strict';

	$( '.rwmb-post-add-button' ).rwmbModal( {
		$postId: null,
		removeElement: '#editor .interface-interface-skeleton__footer, .edit-post-fullscreen-mode-close',
		callback: function ( $modal ) {
			if ( !this.isBlockEditor ) {
				this.$postId = $modal.find( '#post_ID' ).val();
				return;
			}

			setTimeout( () => {
				if ( $modal.find( '.edit-post-post-url .edit-post-post-url__toggle' ).length > 0 ) {
					let url = $modal.find( '.edit-post-post-url .edit-post-post-url__toggle' ).text();
					this.$postId = url.substr( url.indexOf( "=" ) + 1 );
				}
			}, 2000 );

			setTimeout( () => {
				const $ui = $modal.find( '.interface-interface-skeleton' );
				$ui.css( {
					left: 0,
					top: 0
				} );
				$ui.find( '.interface-interface-skeleton__editor' ).css( 'overflow', 'scroll' );
			}, 500 );
		},
		closeModalCallback: function ( $modal, $input ) {
			const $postTitle = !this.isBlockEditor ? $modal.find( '#title' ).val() : $modal.find( '.interface-interface-skeleton__editor h1.editor-post-title__input' ).text().trim();

			if ( !this.$postId || !$postTitle || $postTitle !== '' ) {
				return;
			}

			if ( $input.find( '> *[data-options]' ).length > 1 || $input.find( '.rwmb-select-tree, .rwmb-select' ).length > 0 ) {
				$input.find( 'select' ).attr( 'data-selected', this.$postId );
				$input.find( 'select :selected' ).removeAttr( 'selected' );

				if ( $input.find( '.rwmb-select' ).length > 0 ) {
					return;
				}

				$input.find( 'select' ).prepend( $( '<option>', {
					value: this.$postId,
					text: $postTitle,
					selected: true
				} ) );

				return;
			}

			//Input List ( checkbox or Radio )
			if ( $input.find( '.rwmb-input-list' ).length > 0 ) {
				$input.find( '.rwmb-input-list' ).attr( 'data-selected', this.$postId );
			}
		}
	} );

} )( jQuery, rwmb );
