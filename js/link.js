( ( $ ) => {
	'use strict';

	/**
	 * Get or create a shared hidden textarea for wpLink.
	 * wpLink.open() requires a textarea element with the given editor ID.
	 */
	const getLinkTextarea = () => {
		let $textarea = $( '#rwmb-link-textarea' );
		if ( ! $textarea.length ) {
			$textarea = $( '<textarea id="rwmb-link-textarea" style="display:none"></textarea>' ).appendTo( 'body' );
		}
		return $textarea;
	};

	$( document ).on( 'click', '.rwmb-link-select, .rwmb-link-edit', ( e ) => {
		e.preventDefault();

		if ( typeof wpLink === 'undefined' ) {
			return;
		}

		const $link     = $( e.target ).closest( '.rwmb-link' ),
			$url       = $link.find( 'input[name$="[url]"]' ),
			$title     = $link.find( 'input[name$="[title]"]' ),
			$target    = $link.find( 'input[name$="[target]"]' ),
			$textarea  = getLinkTextarea(),
			origTitle  = $title.val();

		wpLink.open( $textarea.attr( 'id' ) );

		// Pre-fill the modal after it opens.
		setTimeout( () => {
			$( '#wp-link-url' ).val( $url.val() );
			$( '#wp-link-text' ).val( origTitle );
			$( '#wp-link-target' ).prop( 'checked', $target.val() === '_blank' );
		}, 100 );

		// Watch for URL changes from post search selections.
		let lastUrl = $url.val();
		const pollInterval = setInterval( () => {
			const currentUrl = $( '#wp-link-url' ).val();
			if ( currentUrl && currentUrl !== lastUrl ) {
				lastUrl = currentUrl;
				// Try to get title from selected search result, then selectedPost data.
				let title = $( '#wp-link-wrap .query-results li.selected .item-title' ).text().trim()
					|| $( '#wp-link-wrap' ).data( 'selectedPost' )
					|| '';
				if ( typeof title === 'object' && title.title ) {
					title = title.title;
				}
				if ( title ) {
					$( '#wp-link-text' ).val( title );
				}
			}
		}, 100 );

		$( '#wp-link-submit' ).off( 'click.rwmb-link' ).on( 'click.rwmb-link', () => {
			clearInterval( pollInterval );

			const attrs = wpLink.getAttrs();
			if ( ! attrs.href ) {
				return;
			}

			$url.val( attrs.href );
			$target.val( attrs.target === '_blank' ? '_blank' : '' );
			$title.val( $( '#wp-link-text' ).val() || attrs.href );

			const postData = $( '#wp-link-wrap' ).data( 'selectedPost' );
			$link.find( 'input[name$="[post_id]"]' ).val( postData && postData.ID ? postData.ID : 0 );

			const titleVal  = $title.val();
			const targetVal = $target.val();
			const $display  = $( '<span class="rwmb-link-text"></span>' );

			$display.append( '<span class="dashicons dashicons-admin-links"></span> ' );
			$display.append( $( '<a>', { href: attrs.href, target: '_blank', text: titleVal } ) );
			if ( targetVal === '_blank' ) {
				$display.append( ' <span class="rwmb-link-target">' + rwmbLink.newTabText + '</span>' );
			}

			const $wrapper = $( '<div></div>' );
			$wrapper.append( $display );
			$wrapper.append( $( '<a>', { href: '#', class: 'rwmb-link-edit', text: rwmbLink.editText } ) );
			$wrapper.append( $( '<a>', { href: '#', class: 'rwmb-link-remove', text: rwmbLink.removeText } ) );

			$link.find( '.rwmb-link-display' ).empty().append( $wrapper.children() );
			wpLink.close();
		} );

		$( '#wp-link-cancel' ).off( 'click.rwmb-link' ).on( 'click.rwmb-link', () => {
			clearInterval( pollInterval );
			wpLink.close();
		} );
	} );

	$( document ).on( 'click', '.rwmb-link-remove', ( e ) => {
		e.preventDefault();

		const $link = $( e.target ).closest( '.rwmb-link' );
		$link.find( 'input[name$="[url]"]' ).val( '' );
		$link.find( 'input[name$="[title]"]' ).val( '' );
		$link.find( 'input[name$="[target]"]' ).val( '' );
		$link.find( 'input[name$="[post_id]"]' ).val( 0 );
		$link.find( '.rwmb-link-display' ).html( '<a href="#" class="rwmb-link-select">' + rwmbLink.selectLinkText + '</a>' );
	} );

} )( jQuery );
