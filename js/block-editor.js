( function( wp, rwmb ) {
	// Fix missing methods in isolated block editor.
	if ( typeof _.pluck !== 'function' && typeof _.map === 'function' ) {
		_.pluck = ( collection, property ) => _.map( collection, property );
	}

	if ( typeof _.contains !== 'function' && typeof _.includes === 'function' ) {
		_.contains = _.includes;
	}

	const transform = textarea => {
		// Remove the clone editor from the DOM if it exists
		const editor = textarea.nextElementSibling;
		if ( editor && editor.classList.contains( 'editor' ) ) {
			editor.remove();
		}

		const settings = JSON.parse( textarea.dataset.settings );

		if ( settings.upload ) {
			wp.hooks.addFilter( 'editor.MediaUpload', 'rwmb/media-upload', () => wp.mediaUtils.MediaUpload );
			settings.editor = { mediaUpload: wp.editor.mediaUpload };
		}
		wp.attachEditor( textarea, settings );
	};

	const init = () => {
		document.querySelectorAll( '.rwmb-block_editor-wrapper textarea' ).forEach( transform );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-block_editor', function() {
			// Transform a textarea to an editor is a heavy task.
			// Moving it to the end of task queue with setTimeout makes cloning faster.
			setTimeout( () => transform( this ), 200 );
		} );
} )( wp, rwmb );