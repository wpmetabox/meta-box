( function ( $, rwmb ) {
	'use strict';

	rwmb.postThumbnail = {
		applyTemplates: function ( options ) {
			var show = options.show_thumbnail
				|| ( options.ajax_data && options.ajax_data.field && options.ajax_data.field.show_thumbnail );

			if ( ! show ) {
				return options;
			}

			options.templateResult = function ( data ) {
				if ( ! data.id ) {
					return data.text;
				}
				var $wrap = $( '<span class="rwmb-post-option"></span>' );
				var thumb = data.thumbnail || ( data.element ? $( data.element ).data( 'thumbnail' ) : '' );

				if ( thumb ) {
					$wrap.append( $( '<img>', {
						src: thumb,
						class: 'rwmb-post-thumbnail',
						width: 20,
						height: 20,
						alt: ''
					} ) );
				} else {
					$wrap.append( $( '<span class="rwmb-post-thumbnail rwmb-post-thumbnail--empty"></span>' ) );
				}

				$wrap.append( document.createTextNode( ' ' + ( data.text || '' ) ) );
				return $wrap;
			};
			options.templateSelection = options.templateResult;
			return options;
		}
	};
} )( jQuery, rwmb );
