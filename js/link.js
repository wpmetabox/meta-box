( ( $, rwmb, i18n ) => {
	'use strict';

	/**
	 * Get or create a shared hidden textarea for wpLink.
	 * wpLink.open() requires a textarea element with the given editor ID.
	 */
	const textareaId = 'rwmb-link-textarea';
	$( `<textarea id="${ textareaId }" style="display:none"></textarea>` ).appendTo( 'body' );

	rwmb.$document.on( 'click', '.rwmb-link-select, .rwmb-link-edit', ( e ) => {
		e.preventDefault();

		if ( typeof wpLink === 'undefined' ) {
			return;
		}

		const $link = $( e.target ).closest( '.rwmb-link' ),
			$url    = $link.find( 'input[name$="[url]"]' ),
			$title  = $link.find( 'input[name$="[title]"]' ),
			$target = $link.find( 'input[name$="[target]"]' ),
			$postId = $link.find( 'input[name$="[post_id]"]' );

		wpLink.open( textareaId );

		// Pre-fill the modal after it opens.
		setTimeout( () => {
			$( '#wp-link-url' ).val( $url.val() );
			$( '#wp-link-text' ).val( $title.val() );
			$( '#wp-link-target' ).prop( 'checked', $target.val() === '_blank' );
		}, 100 );

		$( '#wp-link-submit' ).off( 'click.rwmb-link' ).on( 'click.rwmb-link', () => {
			const attrs = wpLink.getAttrs();
			if ( ! attrs.href ) {
				return;
			}

			$url.val( attrs.href );
			$title.val( $( '#wp-link-text' ).val() || attrs.href );
			$target.val( attrs.target === '_blank' ? '_blank' : '' );

			const postData = $( '#wp-link-wrap' ).data( 'selectedPost' );
			$postId.val( postData && postData.ID ? postData.ID : 0 );

			const $display  = $( '<span class="rwmb-link-text"></span>' );
			$display.append( '<span class="dashicons dashicons-admin-links"></span> ' );
			$display.append( $( '<a>', { href: attrs.href, target: '_blank', text: $title.val() } ) );
			if ( $target.val() === '_blank' ) {
				$display.append( ' <span class="rwmb-link-target">' + i18n.newTabText + '</span>' );
			}

			const $wrapper = $( '<div></div>' );
			$wrapper.append( $display );
			$wrapper.append( $( '<a>', { href: '#', class: 'rwmb-link-edit', text: i18n.editText } ) );
			$wrapper.append( $( '<a>', { href: '#', class: 'rwmb-link-remove', text: i18n.removeText } ) );

			$link.find( '.rwmb-link-display' ).empty().append( $wrapper.children() );
			wpLink.close();
		} );

		$( '#wp-link-cancel' ).off( 'click.rwmb-link' ).on( 'click.rwmb-link', () => {
			wpLink.close();
		} );
	} );

	rwmb.$document.on( 'click', '.rwmb-link-remove', ( e ) => {
		e.preventDefault();

		const $link = $( e.target ).closest( '.rwmb-link' );
		$link.find( 'input[name$="[url]"]' ).val( '' );
		$link.find( 'input[name$="[title]"]' ).val( '' );
		$link.find( 'input[name$="[target]"]' ).val( '' );
		$link.find( 'input[name$="[post_id]"]' ).val( 0 );
		$link.find( '.rwmb-link-display' ).html( '<button class="button rwmb-link-select">' + i18n.selectLinkText + '</button>' );
	} );

} )( jQuery, rwmb, rwmbLink );
