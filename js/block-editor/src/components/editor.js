import {
	BlockCanvas,
	BlockEditorProvider,
	BlockInspector,
	BlockNavigationDropdown,
	Inserter,
} from '@wordpress/block-editor';
import { serialize } from '@wordpress/blocks';
import { Button, Flex } from '@wordpress/components';
import { useStateWithHistory } from '@wordpress/compose';
import { createPortal, useEffect, useReducer } from '@wordpress/element';
import '@wordpress/format-library';
import { __ } from '@wordpress/i18n';
import {
	drawerRight,
	fullscreen,
	redo as redoIcon,
	undo as undoIcon,
} from '@wordpress/icons';
import { getEditorSettings, getPortalRoot, parseContent } from '../functions';

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

	const settings = JSON.parse( textarea.dataset.settings );
	const editorSettings = getEditorSettings( settings );

	// Disable body scroll in fullscreen
	useEffect( () => {
		document.body.style.overflow = isFullscreen ? 'hidden' : '';

		return () => {
			document.body.style.overflow = '';
		};
	}, [ isFullscreen ] );

	const editor = (
		<BlockEditorProvider
			value={ value.blocks }
			onChange={ persistBlocks }
			settings={ editorSettings }
		>
			<Flex justify="space-between" className="rwmb-block-editor__toolbar">
				<Flex justify="flex-start">
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
					<BlockNavigationDropdown />
				</Flex>

				<Flex justify="flex-end">
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
				<div className="rwmb-block-editor__content editor-styles-wrapper">
					<BlockCanvas height={ settings.height } />
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
			<div className="rwmb-block-editor__canvas rwmb-block-editor__canvas--fullscreen">
				{ editor }
			</div>,
			getPortalRoot()
		);
	}

	return <div className="rwmb-block-editor__canvas">{ editor }</div>;
}
