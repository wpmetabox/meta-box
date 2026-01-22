import {
	BlockCanvas,
	BlockEditorProvider,
	BlockToolbar,
} from '@wordpress/block-editor';
import { serialize } from '@wordpress/blocks';
import '@wordpress/editor'; // This shouldn't be necessary
import { useState } from '@wordpress/element';
import '@wordpress/format-library';

export default function( { textarea } ) {
	const [ blocks, updateBlocks ] = useState( [] );

	const persistBlocks = blocks => {
		updateBlocks( blocks );
		textarea.value = serialize( blocks );
	};

	return (
		<BlockEditorProvider
			value={ blocks }
			onInput={ updateBlocks }
			onChange={ persistBlocks }
			settings={ {
				hasFixedToolbar: true,
			} }
		>
			<BlockToolbar />
			<BlockCanvas height="500px" />
		</BlockEditorProvider>
	);
}
