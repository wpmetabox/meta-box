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

		// Add mediaUpload function if enableUpload is true and user has permission
		if ( settings.editor && settings.editor.enableUpload ) {
			// Check if wp.mediaUtils is available
			if ( window.wp && window.wp.mediaUtils && typeof window.wp.mediaUtils.uploadMedia === 'function' ) {
				// Create mediaUpload function wrapper
				settings.editor.mediaUpload = ( { onError, ...args } ) => {
					try {
						window.wp.mediaUtils.uploadMedia( {
							wpAllowedMimeTypes: settings.editor.allowedMimeTypes || {},
							onError: ( error ) => {
								if ( onError ) {
									const errorMessage = error && error.message ? error.message : ( typeof error === 'string' ? error : 'Upload failed' );
									onError( errorMessage );
								}
							},
							...args
						} );
					} catch ( error ) {
						if ( onError ) {
							onError( error.message || 'Upload failed' );
						}
					}
				};
			} else {
				// Log warning if mediaUtils is not available
				console.warn( 'RWMB Block Editor: wp.mediaUtils.uploadMedia is not available. Media upload may not work.' );
			}
		}

		// Mark as initialized BEFORE calling attachEditor to prevent race conditions
		wrapper.dataset.initialized = 'true';
		textarea.dataset.hasEditor = 'true';

		try {
			attachEditor( textarea, settings );

			// Setup auto-close inserter popover after block is inserted
			setupAutoCloseInserter( wrapper );
		} catch ( error ) {
			console.error( 'RWMB Block Editor: failed to initialize editor instance.', error );
			// Reset flags on error so it can be retried
			wrapper.dataset.initialized = 'false';
			textarea.dataset.hasEditor = 'false';
		}
	};

	const setupAutoCloseInserter = ( wrapper ) => {
		// Wait a bit for editor to be fully initialized
		setTimeout( () => {
			// Method 1: Use wp.data to monitor block insertion
			if ( window.wp && window.wp.data ) {
				try {
					const { subscribe, select, dispatch } = window.wp.data;
					const store = 'isolated/editor';

					// Check if store exists
					if ( select( store ) ) {
						let previousBlockCount = 0;
						let isInserterOpened = false;
						let lastInserterStateCheck = 0;

						// Monitor inserter state and block count
						const unsubscribe = subscribe( () => {
							try {
								const now = Date.now();

								// Check inserter state (throttle to avoid too many checks)
								if ( now - lastInserterStateCheck > 50 ) {
									const currentInserterState = select( store ).isInserterOpened();
									if ( currentInserterState !== isInserterOpened ) {
										isInserterOpened = currentInserterState;
									}
									lastInserterStateCheck = now;
								}

								// Check block count
								const blocks = select( 'core/block-editor' )?.getBlocks();
								const currentBlockCount = blocks ? blocks.length : 0;

								// If inserter is open and block count increased, close it
								if ( isInserterOpened && currentBlockCount > previousBlockCount ) {
									// Close inserter via store
									dispatch( store ).setIsInserterOpened( false );
									isInserterOpened = false;
									previousBlockCount = currentBlockCount;
									return; // Exit early after closing
								}

								previousBlockCount = currentBlockCount;
							} catch ( e ) {
								// Store might not be ready, use DOM fallback
							}
						} );

						// Cleanup when wrapper is removed
						const cleanupObserver = new MutationObserver( () => {
							if ( ! document.body.contains( wrapper ) ) {
								unsubscribe();
								cleanupObserver.disconnect();
							}
						} );
						cleanupObserver.observe( document.body, { childList: true, subtree: true } );
					}
				} catch ( e ) {
					// Fall through to DOM-based approach
				}
			}

			// Method 2: DOM-based fallback - listen for clicks on inserter items
			setupAutoCloseInserterDOM( wrapper );
		}, 500 );
	};

	const closePopoverDirectly = ( wrapper ) => {
		// ONLY use store API - never manipulate DOM directly to avoid React conflicts
		if ( ! window.wp || ! window.wp.data ) {
			return;
		}

		try {
			const { select, dispatch } = window.wp.data;
			const store = 'isolated/editor';

			// Check if store exists
			if ( ! select( store ) ) {
				return;
			}

			// Always try to close inserter (even if state check fails)
			// This ensures it closes even if state is out of sync
			try {
				dispatch( store ).setIsInserterOpened( false );
			} catch ( e ) {
				// If that fails, try checking state first
				try {
					if ( select( store ).isInserterOpened() ) {
						dispatch( store ).setIsInserterOpened( false );
					}
				} catch ( e2 ) {
					// Store method failed - silently ignore
					// React will handle the popover lifecycle
				}
			}
		} catch ( error ) {
			// Silently ignore - React manages the DOM, we shouldn't interfere
		}
	};

	const setupAutoCloseInserterDOM = ( wrapper ) => {
		// Track if we've already set up listeners to avoid duplicates
		if ( wrapper.dataset.inserterCloseSetup === 'true' ) {
			return;
		}
		wrapper.dataset.inserterCloseSetup = 'true';

		// Listen for clicks on block inserter menu items
		const handleBlockInsert = ( event ) => {
			const target = event.target;
			const inserterItem = target.closest(
				'.block-editor-inserter__menu-item, ' +
				'.block-editor-block-types-list__list-item, ' +
				'[role="option"][aria-label], ' +
				'.block-editor-inserter__block-list-item, ' +
				'.block-editor-inserter__block, ' +
				'button[aria-label*="block"], ' +
				'button[aria-label*="Block"]'
			);

			if ( inserterItem ) {
				// Block was clicked, close popover immediately and after delays
				// Try multiple times to ensure it closes
				closePopoverDirectly( wrapper );

				requestAnimationFrame( () => {
					closePopoverDirectly( wrapper );
					setTimeout( () => {
						closePopoverDirectly( wrapper );
					}, 50 );
					setTimeout( () => {
						closePopoverDirectly( wrapper );
					}, 150 );
				} );
			}
		};

		// Listen for clicks (capture phase to catch early)
		wrapper.addEventListener( 'click', handleBlockInsert, true );
		wrapper.addEventListener( 'mousedown', handleBlockInsert, true );

		// Also listen for keyboard events (Enter/Space on inserter items)
		wrapper.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Enter' || event.key === ' ' ) {
				const target = event.target;
				const inserterItem = target.closest(
					'.block-editor-inserter__menu-item, ' +
					'.block-editor-block-types-list__list-item, ' +
					'[role="option"][aria-label], ' +
					'.block-editor-inserter__block-list-item, ' +
					'.block-editor-inserter__block, ' +
					'button[aria-label*="block"], ' +
					'button[aria-label*="Block"]'
				);

				if ( inserterItem ) {
					// Try multiple times to ensure it closes
					closePopoverDirectly( wrapper );

					requestAnimationFrame( () => {
						closePopoverDirectly( wrapper );
						setTimeout( () => {
							closePopoverDirectly( wrapper );
						}, 50 );
						setTimeout( () => {
							closePopoverDirectly( wrapper );
						}, 150 );
					} );
				}
			}
		}, true );

		// Also use MutationObserver to detect when popover appears and monitor for block insertion
		const popoverObserver = new MutationObserver( () => {
			// Check if popover is visible
			const popover = wrapper.querySelector( '.components-popover.is-positioned' );
			if ( popover ) {
				// Monitor for block insertion by watching for changes in block list
				let previousBlockCount = 0;
				const blockListObserver = new MutationObserver( () => {
					// Check if a new block was added
					const blocks = wrapper.querySelectorAll( '.block-editor-block-list__block' );
					const currentBlockCount = blocks.length;

					if ( currentBlockCount > previousBlockCount ) {
						// Block was added, close popover
						closePopoverDirectly( wrapper );

						requestAnimationFrame( () => {
							closePopoverDirectly( wrapper );
							setTimeout( () => {
								closePopoverDirectly( wrapper );
							}, 100 );
							setTimeout( () => {
								closePopoverDirectly( wrapper );
							}, 250 );
						} );
					}

					previousBlockCount = currentBlockCount;
				} );

				// Observe the editor content area (but only use store API, never manipulate DOM)
				const editorRoot = wrapper.querySelector( '.rwmb-block-editor-root, .block-editor-writing-flow' );
				if ( editorRoot ) {
					blockListObserver.observe( editorRoot, { childList: true, subtree: true } );

					// Cleanup after popover is closed (check via store, not DOM)
					const cleanupObserver = new MutationObserver( () => {
						// Check via store instead of DOM query
						if ( window.wp && window.wp.data ) {
							try {
								const { select } = window.wp.data;
								const store = 'isolated/editor';
								if ( select( store ) && ! select( store ).isInserterOpened() ) {
									blockListObserver.disconnect();
									cleanupObserver.disconnect();
								}
							} catch ( e ) {
								// Ignore
							}
						}
					} );
					cleanupObserver.observe( wrapper, { childList: true, subtree: true } );
				}
			}
		} );

		popoverObserver.observe( wrapper, { childList: true, subtree: true } );
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
		// Setup global filter to override MediaUploadCheck for isolated editors
		// This fixes the "To edit this block, you need permission to upload media" error
		// Permission is already checked in PHP via current_user_can('upload_files')
		if ( window.wp && window.wp.hooks && window.wp.element ) {
			window.wp.hooks.addFilter(
				'editor.MediaUploadCheck',
				'rwmb/block-editor/media-upload-check',
				( OriginalComponent ) => {
					return ( props ) => {
						// Check if we're in a Meta Box isolated editor context
						// If any isolated editor wrapper exists with enableUpload, allow it
						const allWrappers = document.querySelectorAll( '.rwmb-block-editor-wrapper' );
						for ( let i = 0; i < allWrappers.length; i++ ) {
							try {
								const settings = parseSettings( allWrappers[i].getAttribute( 'data-settings' ) );
								// If enableUpload is true, always allow (permission already checked in PHP)
								if ( settings.editor && settings.editor.enableUpload ) {
									// Check if this component is rendered within this isolated editor
									const editorElement = allWrappers[i].querySelector( '.editor, .block-editor-writing-flow' );
									if ( editorElement ) {
										// Always allow in isolated editor if enableUpload is true
										// The permission was already verified in PHP
										return props.children;
									}
								}
							} catch ( e ) {
								// Continue to next wrapper
							}
						}

						// Otherwise use original component (for regular WordPress editor)
						return window.wp.element.createElement( OriginalComponent, props );
					};
				},
				10
			);
		}

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

