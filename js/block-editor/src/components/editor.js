import {
	BlockCanvas,
	BlockEditorProvider,
	BlockInspector,
	BlockToolbar,
	Inserter,
} from '@wordpress/block-editor';
import { parse, rawHandler, serialize } from '@wordpress/blocks';
import { Button, Flex } from '@wordpress/components';
import { useStateWithHistory } from '@wordpress/compose';
import { useState } from '@wordpress/element';
import '@wordpress/format-library';
import { drawerRight, redo as redoIcon, undo as undoIcon } from '@wordpress/icons';

const parseContent = content => content.includes( '<!--' ) ? parse( content ) : rawHandler( { HTML: content } );

export default function( { textarea } ) {
	const { value, setValue, hasUndo, hasRedo, undo, redo } = useStateWithHistory( { blocks: [] } );
	const [ blocks, updateBlocks ] = useState( parseContent( textarea.value ) );
	const [ isSidebarOpen, setIsSidebarOpen ] = useState( false );

	const persistBlocks = blocks => {
		updateBlocks( blocks );
		textarea.value = serialize( blocks );
	};

	const inserterProps = {
		size: 'compact',
		variant: 'primary',
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
			<Flex align="center" justify="space-between" className="rwmb-block-editor__toolbar">
				<Flex align="center" justify="flex-start" className="rwmb-block-editor__toolbar-left">
					<Inserter toggleProps={ inserterProps } />
					<Button
						onClick={ undo }
						disabled={ !hasUndo }
						accessibleWhenDisabled
						icon={ undoIcon }
						label="Undo"
						size="compact"
					/>
					<Button
						onClick={ redo }
						disabled={ !hasRedo }
						accessibleWhenDisabled
						icon={ redoIcon }
						label="Redo"
						size="compact"
					/>
					<BlockToolbar hideDragHandle />
				</Flex>
				<Button
					icon={ drawerRight }
					aria-pressed={ isSidebarOpen }
					label="Toggle Sidebar"
					size="compact"
					onClick={ () => setIsSidebarOpen( !isSidebarOpen ) }
				/>
			</Flex>
			<Flex gap={ 0 } align="stretch" className="rwmb-block-editor__main">
				<div className="rwmb-block-editor__content">
					<BlockCanvas height="500px" />
				</div>
				{
					isSidebarOpen && (
						<div className="rwmb-block-editor__sidebar">
							<BlockInspector />
						</div>
					)
				}
			</Flex>
		</BlockEditorProvider>
	);
}
