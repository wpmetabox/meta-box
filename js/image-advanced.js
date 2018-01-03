window.rwmb = window.rwmb || {};

jQuery( function ( $ ) {
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
					className: 'rwmb-image-item attachment',
					template: wp.template( 'rwmb-image-item' ),
					initialize: function( models, options ) {
						MediaItem.prototype.initialize.call( this, models, options );
						this.$el.addClass( this.controller.get( 'imageSize' ) );
					}
				} )
			} );
		}
	} );

	/**
	 * Initialize image fields
	 */
	function initImageField() {
		var view = new ImageField( { input: this } );
		$( this ).after( view.el );
	}

	/**
	 * Remove views for uploaded images.
	 */
	function removeView() {
		$( this ).siblings( '.rwmb-media-view' ).remove();
	}

	$( '.rwmb-image_advanced' ).each( initImageField );
	$( document )
		.on( 'clone', '.rwmb-image_advanced', removeView )
		.on( 'after_clone', '.rwmb-image_advanced', initImageField );
} );
