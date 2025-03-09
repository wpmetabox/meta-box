{
	// Auto add UTM params to links.
	document.querySelectorAll( '.mb-dashboard a' ).forEach( a => {
		if ( a.href.startsWith( 'https://metabox.io' ) ) {
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
}