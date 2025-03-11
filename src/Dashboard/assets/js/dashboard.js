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

		fetch( `${ ajaxurl }?action=mb_dashboard_plugin_action&mb_plugin=${ el.dataset.plugin }&mb_action=${ el.dataset.action }&_ajax_nonce=${ MBD.nonces.plugin }` )
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

		document.querySelectorAll( '.mb-dashboard__tab-pane' ).forEach( e => e.classList.add( 'mb-dashboard__hidden' ) );
		document.querySelector( `.mb-dashboard__tab-pane[data-tab="${ el.dataset.tab }"]` ).classList.remove( 'mb-dashboard__hidden' );
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
			// Original docs
			const originalDocs = data[ 0 ].documents.map( ( { t, u } ) => ( { t, u } ) );

			// Sections
			const findOriginalDoc = url => originalDocs.find( ( { t, u } ) => u === url );

			// Section docs.
			const sectionDocs = data[ 1 ].documents.filter( doc => doc.h.length > 0 && !doc.u.includes( '/category/' ) ).map( ( { t, u, h } ) => {
				const originalDoc = findOriginalDoc( u );
				if ( !originalDoc ) {
					return {
						t,
						u: `${ u }${ h }`,
					};
				}

				return {
					t: `${ originalDoc.t } → ${ t }`,
					u: `${ u }${ h }`,
				};
			} );

			return [ ...originalDocs, ...sectionDocs ];
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

	const fetchNews = async () => {
		const newsDiv = document.querySelector( '.mb-dashboard__news' );

		newsDiv.innerHTML = newsDiv.dataset.fetching;

		let response = await fetch( `${ ajaxurl }?action=mb_dashboard_feed&_ajax_nonce=${ MBD.nonces.feed }` );
		response = await response.json();

		if ( ! response.success ) {
			alert( response.data );
			newsDiv.innerHTML = newsDiv.dataset.empty;
			return;
		}

		let lastUpdated = 0;
		const items = response.data.map( item => {
			const url = new URL( item.url );
			url.searchParams.append( 'utm_source', 'dashboard' );
			url.searchParams.append( 'utm_medium', 'news' );
			url.searchParams.append( 'utm_campaign', 'meta_box' );

			if ( lastUpdated < item.timestamp ) {
				lastUpdated = item.timestamp;
			}

			return `<div class="mb-dashboard__news__item">
				<a class="mb-dashboard__news__title" href="${ url }" target="_blank">${ item.title }</a>
				<div class="mb-dashboard__news__date">${ item.date }</div>
				<div class="mb-dashboard__news__content">${ item.content }</div>
			</div>`;
		} );

		newsDiv.innerHTML = items.join( '' );
	};

	fetchNews();
}