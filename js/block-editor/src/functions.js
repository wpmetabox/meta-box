import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as blocksStore, parse, rawHandler } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
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
	let settings = select( blockEditorStore ).getSettings();
	let blockTypes = select( blocksStore ).getBlockTypes() || [];

	if ( Array.isArray( allowed_blocks ) && allowed_blocks.length > 0 ) {
		blockTypes = blockTypes.filter( block => allowed_blocks.includes( block.name ) );
	}

	// TODO: Add allowed block types to the editor settings
	// settings.allowedBlockTypes = blockTypes;

	return settings;
} );