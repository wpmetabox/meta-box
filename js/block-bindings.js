( function ( wp, data ) {
	'use strict';

	if ( ! wp?.blocks?.registerBlockBindingsSource || ! data?.fields ) {
		return;
	}

	// The front-end value is resolved in PHP. In the editor we only expose the
	// fields list so the source appears in the block bindings UI. Bound blocks
	// show their placeholder until the post is viewed on the front end.
	//
	// getValues is required by the editor even though we have no client-side
	// value (the field data is not exposed via REST). Return an empty object so
	// the bindings UI renders without previewing a value.
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
