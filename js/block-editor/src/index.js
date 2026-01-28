import { registerCoreBlocks } from '@wordpress/block-library';
import { createRoot } from '@wordpress/element';
import Editor from './components/editor';
import './style.scss';

// Make sure to load stylesheets:
// import '@wordpress/block-editor/build-style/style.css'; // wp-block-editor
// import '@wordpress/components/build-style/style.css'; // wp-components

// Make sure to load stylesheets:
// import '@wordpress/block-library/build-style/editor.css'; // wp-edit-blocks
// import '@wordpress/block-library/build-style/style.css'; // wp-block-library
// import '@wordpress/block-library/build-style/theme.css'; // wp-block-library-theme

registerCoreBlocks();

function attachEditor( textarea ) {
	// Create a node after the textarea
	const editor = document.createElement( 'div' );
	editor.classList.add( 'rwmb-block-editor' );
	const root = createRoot( editor );

	// Insert after the textarea, and hide it
	textarea.parentNode.insertBefore( editor, textarea.nextSibling );
	textarea.style.display = 'none';

	root.render( <Editor textarea={ textarea } /> );
}

const transform = textarea => {
	// Remove the clone editor from the DOM if it exists
	const editor = textarea.nextElementSibling;
	if ( editor && editor.classList.contains( 'rwmb-block-editor' ) ) {
		editor.remove();
	}

	attachEditor( textarea );
};

const init = () => {
	document.querySelectorAll( '.rwmb-block_editor-wrapper textarea' ).forEach( transform );
};

rwmb.$document
	.on( 'mb_ready', init )
	.on( 'clone', '.rwmb-block_editor', function() {
		// Transform a textarea to an editor is a heavy task.
		// Moving it to the end of task queue with setTimeout makes cloning faster.
		setTimeout( () => transform( this ), 200 );
	} );