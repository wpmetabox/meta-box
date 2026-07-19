( function ( wp, data ) {
	'use strict';

	if ( ! wp?.blocks?.registerBlockBindingsSource || ! data?.fields ) {
		return;
	}

	// The front-end value is resolved in PHP. In the editor we only expose the
	// fields list so the source appears in the block bindings UI. Bound blocks
	// show their placeholder until the post is viewed on the front end.
	wp.blocks.registerBlockBindingsSource( {
		name: 'meta-box/field',
		label: wp.i18n.__( 'Meta Box Field', 'meta-box' ),
		usesContext: [ 'postId', 'postType' ],
		getFieldsList( { context } ) {
			return data.fields[ context?.postType ] || [];
		},
		canUserEditValue: () => false,
	} );
} )( window.wp, window.rwmbBlockBindings );
