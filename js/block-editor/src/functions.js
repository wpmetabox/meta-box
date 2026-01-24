import { parse, rawHandler } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import '@wordpress/format-library';

export const parseContent = content => content.includes( '<!--' ) ? parse( content ) : rawHandler( { HTML: content } );

export const getPortalRoot = () => {
	let el = document.getElementById( 'rwmb-block-editor-portal' );

	if ( !el ) {
		el = document.createElement( 'div' );
		el.id = 'rwmb-block-editor-portal';
		document.body.appendChild( el );
	}

	return el;
};

export const getEditorSettings = ( { allowed_blocks } ) => useSelect( select => {
	const settings = select( blockEditorStore ).getSettings();

	if ( Array.isArray( allowed_blocks ) && allowed_blocks.length > 0 ) {
		settings.allowedBlockTypes = allowed_blocks;
	}

	return settings;
} );