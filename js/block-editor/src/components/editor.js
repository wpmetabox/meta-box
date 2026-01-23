import {
	BlockCanvas,
	BlockEditorProvider,
	BlockInspector,
	BlockToolbar,
	Inserter,
	store,
} from '@wordpress/block-editor';
import { parse, rawHandler, serialize } from '@wordpress/blocks';
import { Button, Flex } from '@wordpress/components';
import { useStateWithHistory } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { createPortal, useEffect, useReducer } from '@wordpress/element';
import '@wordpress/format-library';
import { __ } from '@wordpress/i18n';
import {
	drawerRight,
	fullscreen,
	redo as redoIcon,
	undo as undoIcon,
} from '@wordpress/icons';

const parseContent = content => content.includes( '<!--' ) ? parse( content ) : rawHandler( { HTML: content } );

const getPortalRoot = () => {
	let el = document.getElementById( 'rwmb-block-editor-portal' );

	if ( !el ) {
		el = document.createElement( 'div' );
		el.id = 'rwmb-block-editor-portal';
		document.body.appendChild( el );
	}

	return el;
};

export default function( { textarea } ) {
	const { value, setValue, hasUndo, hasRedo, undo, redo } = useStateWithHistory( { blocks: parseContent( textarea.value ) } );
	const [ isSidebarOpen, toggleSidebar ] = useReducer( state => !state, false );
	const [ isFullscreen, toggleFullscreen ] = useReducer( state => !state, false );

	const persistBlocks = blocks => {
		setValue( { blocks } );
		textarea.value = serialize( blocks );
	};

	const inserterProps = {
		size: 'compact',
		variant: 'primary',
	};

	const settings = useSelect( select => select( store ).getSettings() );
	settings.hasFixedToolbar = true;

	// Disable body scroll in fullscreen
	useEffect( () => {
		if ( isFullscreen ) {
			document.body.style.overflow = 'hidden';
		} else {
			document.body.style.overflow = '';
		}

		return () => {
			document.body.style.overflow = '';
		};
	}, [ isFullscreen ] );

	const editor = (
		<BlockEditorProvider
			value={ value.blocks }
			onChange={ persistBlocks }
			settings={ settings }
		>
			<Flex align="center" justify="space-between" className="rwmb-block-editor__toolbar">
				<Flex align="center" justify="flex-start" className="rwmb-block-editor__toolbar-left">
					<Inserter toggleProps={ inserterProps } />
					<Button
						onClick={ undo }
						disabled={ !hasUndo }
						accessibleWhenDisabled
						icon={ undoIcon }
						label={ __( 'Undo', 'meta-box' ) }
						size="compact"
					/>
					<Button
						onClick={ redo }
						disabled={ !hasRedo }
						accessibleWhenDisabled
						icon={ redoIcon }
						label={ __( 'Redo', 'meta-box' ) }
						size="compact"
					/>
					<BlockToolbar hideDragHandle />
				</Flex>

				<Flex gap={ 1 } justify="flex-end">
					<Button
						icon={ fullscreen }
						aria-pressed={ isFullscreen }
						label={ __( 'Toggle Fullscreen', 'meta-box' ) }
						size="compact"
						onClick={ toggleFullscreen }
					/>
					<Button
						icon={ drawerRight }
						aria-pressed={ isSidebarOpen }
						label={ __( 'Toggle Sidebar', 'meta-box' ) }
						size="compact"
						onClick={ toggleSidebar }
					/>
				</Flex>
			</Flex>

			<Flex gap={ 0 } align="stretch" className="rwmb-block-editor__main">
				<div className="rwmb-block-editor__content">
					<BlockCanvas />
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

	if ( isFullscreen ) {
		return createPortal(
			<div className="rwmb-block-editor rwmb-block-editor--fullscreen">
				{ editor }
			</div>,
			getPortalRoot()
		);
	}

	return (
		<div className="rwmb-block-editor">
			{ editor }
		</div>
	);
}
