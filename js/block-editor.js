( function() {
	'use strict';

	if ( typeof wp === 'undefined' ) {
		return;
	}

	const lodashGlobal = window.lodash || window._;
	if ( lodashGlobal ) {
		if ( ! window._ ) {
			window._ = lodashGlobal;
		}

		if ( typeof window._.pluck !== 'function' && typeof window._.map === 'function' ) {
			window._.pluck = ( collection, property ) => window._.map( collection, property );
		}

		if ( typeof window._.contains !== 'function' && typeof window._.includes === 'function' ) {
			window._.contains = window._.includes;
		}
	}

	const parseSettings = value => {
		if ( ! value ) {
			return {};
		}

		try {
			return JSON.parse( value );
		} catch ( error ) {
			console.error( 'RWMB Block Editor: failed to parse settings.', error );
			return {};
		}
	};

	const initWrapper = ( wrapper, attachEditor ) => {
		if ( ! wrapper || wrapper.dataset.initialized === 'true' ) {
			return;
		}

		const textarea = wrapper.querySelector( 'textarea' );
		if ( ! textarea ) {
			return;
		}

		// Check if textarea already has an editor attached (prevent duplicate initialization)
		if ( textarea.dataset.hasEditor === 'true' ) {
			return;
		}

		const settings = parseSettings( wrapper.getAttribute( 'data-settings' ) );

		// Mark as initialized BEFORE calling attachEditor to prevent race conditions
		wrapper.dataset.initialized = 'true';
		textarea.dataset.hasEditor = 'true';

		try {
			attachEditor( textarea, settings );
		} catch ( error ) {
			console.error( 'RWMB Block Editor: failed to initialize editor instance.', error );
			// Reset flags on error so it can be retried
			wrapper.dataset.initialized = 'false';
			textarea.dataset.hasEditor = 'false';
		}
	};

	const waitForAttachEditor = () => new Promise( ( resolve, reject ) => {
		if ( window.wp && typeof window.wp.attachEditor === 'function' ) {
			resolve( window.wp.attachEditor );
			return;
		}

		const timeout = setTimeout( () => {
			clearInterval( interval );
			reject( new Error( 'timeout' ) );
		}, 10000 );

		const interval = setInterval( () => {
			if ( window.wp && typeof window.wp.attachEditor === 'function' ) {
				clearInterval( interval );
				clearTimeout( timeout );
				resolve( window.wp.attachEditor );
			}
		}, 50 );
	} );

	const bootstrap = () => {
		const wrappers = document.querySelectorAll( '.rwmb-block-editor-wrapper' );
		if ( ! wrappers.length ) {
			return;
		}

		waitForAttachEditor()
			.then( attachEditor => {
				wrappers.forEach( wrapper => initWrapper( wrapper, attachEditor ) );

				if ( typeof MutationObserver === 'undefined' ) {
					return;
				}

				const observer = new MutationObserver( mutations => {
					mutations.forEach( mutation => {
						mutation.addedNodes?.forEach( node => {
							if ( node.nodeType !== 1 ) {
								return;
							}

							if ( node.classList?.contains( 'rwmb-block-editor-wrapper' ) ) {
								initWrapper( node, attachEditor );
								return;
							}

							if ( node.querySelector ) {
								node.querySelectorAll( '.rwmb-block-editor-wrapper' ).forEach( element => initWrapper( element, attachEditor ) );
							}
						} );
					} );
				} );

				observer.observe( document.body, { childList: true, subtree: true } );
			} )
			.catch( error => {
				console.error( 'RWMB Block Editor: attachEditor unavailable.', error );
			} );
	};

	if ( wp.domReady ) {
		wp.domReady( bootstrap );
	} else if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', bootstrap );
	} else {
		bootstrap();
	}
} )();

