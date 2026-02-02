import { parse, rawHandler } from '@wordpress/blocks';
import { store as coreDataStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import '@wordpress/format-library';
import { uploadMedia } from '@wordpress/media-utils';

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
	const settings = rwmbBlockEditor.editor_settings;

	if ( Array.isArray( allowed_blocks ) && allowed_blocks.length > 0 ) {
		settings.allowedBlockTypes = allowed_blocks;
	}

	let canUserCreateMedia = select( coreDataStore ).canUser( 'create', 'media' );
	canUserCreateMedia = canUserCreateMedia || canUserCreateMedia !== false;

	if ( !canUserCreateMedia ) {
		return settings;
	}

	settings.mediaUpload = ( { onError, ...rest } ) => {
		uploadMedia( {
			wpAllowedMimeTypes: settings.allowedMimeTypes,
			onError: ( { message } ) => onError( message ),
			...rest,
		} );
	};

	return settings;
} );