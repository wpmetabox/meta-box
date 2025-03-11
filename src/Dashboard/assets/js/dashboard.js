{
	// Auto add UTM params to links.
	document.querySelectorAll( '.mb-dashboard a' ).forEach( a => {
		if ( a.href.startsWith( 'https://metabox.io' ) || a.href.startsWith( 'https://docs.metabox.io' ) ) {
			a.href += '?utm_source=dashboard&utm_medium=link&utm_campaign=meta_box';
		}
	} );

	// Click to install or activate plugins.
	document.addEventListener( 'click', e => {
		const el = e.target;

		if ( ! el.classList.contains( 'mb-dashboard__plugin__status' ) || ! el.dataset.action ) {
			return;
		}

		const oldText = el.textContent;
		el.textContent = el.dataset.processing;

		fetch( `${ ajaxurl }?action=mb_dashboard_plugin_action&mb_plugin=${ el.dataset.plugin }&mb_action=${ el.dataset.action }&_ajax_nonce=${ MBD.nonce }` )
			.then( response => response.json() )
			.then( response => {
				if ( !response.success ) {
					alert( response.data );
					el.textContent = oldText;
					return;
				}

				el.dataset.action = '';
				el.textContent = el.dataset.done;
			} );
	} );

	// Switch tabs
	document.addEventListener( 'click', e => {
		const el = e.target;

		if ( ! el.classList.contains( 'mb-dashboard__tab' ) ) {
			return;
		}

		document.querySelectorAll( '.mb-dashboard__tab' ).forEach( e => e.classList.remove( 'mb-dashboard__tab--active' ) );
		el.classList.add( 'mb-dashboard__tab--active' );

		document.querySelectorAll( '.mb-dashboard__tab-pane' ).forEach( e => e.classList.add( 'mb-hidden' ) );
		document.querySelector( `.mb-dashboard__tab-pane[data-tab="${ el.dataset.tab }"]` ).classList.remove( 'mb-hidden' );
	} );
}