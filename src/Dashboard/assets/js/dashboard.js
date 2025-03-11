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

		if ( !el.classList.contains( 'mb-dashboard__plugin__status' ) || !el.dataset.action ) {
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

		if ( !el.classList.contains( 'mb-dashboard__tab' ) ) {
			return;
		}

		document.querySelectorAll( '.mb-dashboard__tab' ).forEach( e => e.classList.remove( 'mb-dashboard__tab--active' ) );
		el.classList.add( 'mb-dashboard__tab--active' );

		document.querySelectorAll( '.mb-dashboard__tab-pane' ).forEach( e => e.classList.add( 'mb-hidden' ) );
		document.querySelector( `.mb-dashboard__tab-pane[data-tab="${ el.dataset.tab }"]` ).classList.remove( 'mb-hidden' );
	} );

	const fetchDocs = async () => {
		let docs;

		/**
		 * Check if need to fetch docs.
		 * Docs is cached for 1 month in the local storage.
		 */
		const needsUpdate = () => {
			const lastUpdated = localStorage.getItem( 'meta-box-docs-last-updated' );
			const now = Date.now();
			const MONTH_IN_MILLISECONDS = 30 * 24 * 60 * 60 * 1000;
			if ( !lastUpdated || now - lastUpdated > MONTH_IN_MILLISECONDS ) {
				return true;
			}

			docs = JSON.parse( localStorage.getItem( 'meta-box-docs' ) );
			return !Array.isArray( docs ) || docs.length === 0;
		};

		/**
		 * Normalize docs data, keeping only references to docs titles and sections.
		 */
		const normalizeData = data => {
			const newData = [];

			// Original docs
			data[ 0 ].documents.forEach( doc => {
				newData.push( {
					t: doc.t,
					u: doc.u,
				} );
			} );

			// Sections
			data[ 1 ].documents.forEach( doc => {
				if ( !doc.h || doc.u.includes( '/category/' ) ) {
					return;
				}
				newData.push( {
					t: doc.t,
					u: `${ doc.u }${ doc.h }`,
				} );
			} );

			return newData;
		};

		if ( !needsUpdate() ) {
			return docs;
		}

		let response = await fetch( 'https://docs.metabox.io/search-index.json' );//+
		response = await response.json();//+
		docs = normalizeData( response );
		localStorage.setItem( 'meta-box-docs', JSON.stringify( docs ) );
		localStorage.setItem( 'meta-box-docs-last-updated', Date.now() );

		return docs;
	};

	const searchDocs = async () => {
		const docs = await fetchDocs();
		if ( !Array.isArray( docs ) || docs.length === 0 ) {
			return;
		}

		const resultsDiv = document.querySelector( '.mb-dashboard__header__search-results' );
		const input = document.querySelector( '.mb-dashboard__header__search input' );
		const categories = [ 'fields', 'extensions', 'tutorials', 'integrations', 'actions', 'filters', 'functions' ];

		const search = e => {
			const s = e.target.value;
			if ( s.length === 0 ) {
				resultsDiv.innerHTML = '';
				resultsDiv.dataset.type = 'empty';
				return;
			}

			resultsDiv.innerHTML = resultsDiv.dataset.searching;
			resultsDiv.dataset.type = 'text';

			let results = docs.filter( docs => docs.t.toLowerCase().includes( s.toLowerCase() ) ).slice( 0, 10 );

			if ( results.length === 0 ) {
				resultsDiv.innerHTML = resultsDiv.dataset.none;
				resultsDiv.dataset.type = 'text';
				return;
			}

			results = results.map( result => {
				let category = 'general';
				const parts = result.u.split( '/' );

				if ( categories.includes( parts[ 1 ] ) ) {
					category = parts[ 1 ];
				}
				category = category.charAt( 0 ).toUpperCase() + category.slice( 1 ); // Uppercase the first character.

				const url = new URL( `https://docs.metabox.io${ result.u }` );
				url.searchParams.append( 'utm_source', 'dashboard' );
				url.searchParams.append( 'utm_medium', 'search' );
				url.searchParams.append( 'utm_campaign', 'meta_box' );

				return `<a target="_blank" href="${ url.toString() }">${ result.t } <span>${ category }</span></a>`;
			} );

			resultsDiv.innerHTML = results.join( '' );
			resultsDiv.dataset.type = 'list';
		};

		input.addEventListener( 'input', search );
		input.addEventListener( 'focus', search );

		input.addEventListener( 'blur', () => {
			// Set timeout to make click work properly.
			setTimeout( () => {
				resultsDiv.innerHTML = '';
				resultsDiv.dataset.type = 'empty';
			}, 300 );
		} );
	};

	searchDocs();
}