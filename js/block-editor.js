document.querySelectorAll( '.rwmb-block_editor-wrapper textarea' ).forEach( textarea => {
	const settings = JSON.parse( textarea.dataset.settings );

	if ( settings.upload ) {
		wp.hooks.addFilter( 'editor.MediaUpload', 'rwmb/media-upload', () => wp.mediaUtils.MediaUpload );
		settings.editor = { mediaUpload: wp.editor.mediaUpload };
	}

	wp.attachEditor( textarea, settings );
} );
