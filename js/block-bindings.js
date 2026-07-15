( function ( wp, data ) {
	'use strict';

	if ( ! wp?.blocks?.registerBlockBindingsSource || ! data?.fields ) {
		return;
	}

	const { registerBlockBindingsSource } = wp.blocks;
	const { __ } = wp.i18n;

	registerBlockBindingsSource( {
		name: 'meta-box/field',
		label: __( 'Meta Box Field', 'meta-box' ),
		usesContext: [ 'postId', 'postType' ],
		getFieldsList( { context } ) {
			return data.fields[ context?.postType ] || [];
		},
		getValues( { select, context, bindings } ) {
			const meta = select( 'core' ).getEditedEntityRecord( 'postType', context.postType, context.postId )?.meta || {};
			const values = {};

			for ( const [ attribute, binding ] of Object.entries( bindings ) ) {
				let raw = meta[ binding.args?.id ];
				if ( Array.isArray( raw ) ) {
					raw = raw[ 0 ];
				}

				if ( attribute === 'id' ) {
					values[ attribute ] = raw ? Number( raw.ID ?? raw ) : undefined;
				} else if ( attribute === 'url' || attribute === 'href' ) {
					values[ attribute ] = typeof raw === 'string' && ! /^\d+$/.test( raw )
						? raw
						: select( 'core' ).getMedia( Number( raw ) )?.source_url;
				} else {
					values[ attribute ] = raw != null ? String( raw ) : undefined;
				}
			}

			return values;
		},
		canUserEditValue: () => false,
	} );
} )( window.wp, window.rwmbBlockBindings );
