// Fix missing methods in isolated block editor.
if ( typeof _.pluck !== 'function' && typeof _.map === 'function' ) {
	_.pluck = ( collection, property ) => _.map( collection, property );
}

if ( typeof _.contains !== 'function' && typeof _.includes === 'function' ) {
	_.contains = _.includes;
}

document.querySelectorAll( '.rwmb-block_editor-wrapper textarea' ).forEach( textarea => {
	const settings = JSON.parse( textarea.dataset.settings );

	if ( settings.upload ) {
		wp.hooks.addFilter( 'editor.MediaUpload', 'rwmb/media-upload', () => wp.mediaUtils.MediaUpload );
		settings.editor = { mediaUpload: wp.editor.mediaUpload };
	}

	wp.attachEditor( textarea, settings );
} );
