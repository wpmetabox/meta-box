( function ( wp, data ) {
	'use strict';

	if ( ! wp?.blocks?.registerBlockBindingsSource || ! data?.sources?.length ) {
		return;
	}

	// Front-end values are resolved in PHP. The editor only exposes the fields list.
	// getValues is required by the editor but returns {} (fields are not exposed via REST).
	data.sources.forEach( ( source ) => {
		wp.blocks.registerBlockBindingsSource( {
			name: source.name,
			label: source.label,
			usesContext: source.usesContext,
			getFieldsList( { context } ) {
				if ( ! source.contextKey ) {
					return Array.isArray( source.fields ) ? source.fields : [];
				}
				return source.fields[ context?.[ source.contextKey ] ] || [];
			},
			getValues() {
				return {};
			},
			canUserEditValue: () => false,
		} );
	} );
} )( window.wp, window.rwmbBlockBindings );
