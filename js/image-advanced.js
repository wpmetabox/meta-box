( function ( $, rwmb ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		MediaField = views.MediaField,
		MediaItem = views.MediaItem,
		MediaList = views.MediaList,
		ImageField;

	ImageField = views.ImageField = MediaField.extend( {
		createList: function () {
			this.list = new MediaList( {
				controller: this.controller,
				itemView: MediaItem.extend( {
					className: 'rwmb-image-item',
					template: rwmb.template( `
						<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="rwmb-media-input">
						<div class="rwmb-file-icon">
							<# if ( 'image' === data.type && data.sizes ) { #>
								<# if ( data.sizes[data.controller.imageSize] ) { #>
									<img src="{{{ data.sizes[data.controller.imageSize].url }}}">
								<# } else { #>
									<img src="{{{ data.sizes.full.url }}}">
								<# } #>
							<# } else { #>
								<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
									<img src="{{ data.image.src }}" />
								<# } else { #>
									<img src="{{ data.icon }}" />
								<# } #>
							<# } #>
						</div>
						<div class="rwmb-image-overlay"></div>
						<div class="rwmb-image-actions">
							<a class="rwmb-image-edit rwmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
								<span class="dashicons dashicons-edit"></span>
							</a>
							<a href="#" class="rwmb-image-delete rwmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
								<span class="dashicons dashicons-no-alt"></span>
							</a>
						</div>
					` ),
				} )
			} );
		}
	} );

	/**
	 * Initialize image fields
	 */
	function initImageField() {
		var $this = $( this ),
			view = $this.data( 'view' );

		if ( view ) {
			return;
		}

		view = new ImageField( { input: this } );

		$this.siblings( '.rwmb-media-view' ).remove();
		$this.after( view.el );
		$this.data( 'view', view );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-image_advanced' ).each( initImageField );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-image_advanced', initImageField );

	wp?.hooks?.addAction( 'mb_ready', 'meta-box/ready/image_advanced', ref => {
		init( { target: ref } );
	} );
} )( jQuery, rwmb );
