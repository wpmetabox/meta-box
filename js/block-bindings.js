( function ( wp, data ) {
	'use strict';

	if ( ! wp?.blocks?.registerBlockBindingsSource || ! data?.fields ) {
		return;
	}

	// The front-end value is resolved in PHP; the editor only exposes the fields list so the source appears in the bindings UI.
	// getValues is required by the editor, but there is no client-side value (fields are not exposed via REST), so it returns {}.
	wp.blocks.registerBlockBindingsSource( {
		name: 'meta-box/field',
		label: wp.i18n.__( 'Meta Box Field', 'meta-box' ),
		usesContext: [ 'postId', 'postType' ],
		getFieldsList( { context } ) {
			return data.fields[ context?.postType ] || [];
		},
		getValues() {
			return {};
		},
		canUserEditValue: () => false,
	} );
} )( window.wp, window.rwmbBlockBindings );
