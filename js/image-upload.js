window.rwmb = window.rwmb || {};

jQuery( function ( $ ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		ImageField = views.ImageField,
		ImageUploadField,
		UploadButton = views.UploadButton;

	ImageUploadField = views.ImageUploadField = ImageField.extend( {
		createAddButton: function () {
			this.addButton = new UploadButton( {controller: this.controller} );
		}
	} );

	/**
	 * Initialize fields
	 * @return void
	 */
	function init() {
		var view = new ImageUploadField( { input: this } );
		//Remove old then add new
		$( this ).siblings( 'div.rwmb-media-view' ).remove();
		$( this ).after( view.el );
	}

	$( '.rwmb-image_upload, .rwmb-plupload_image' ).each( init );
	$( document )
		.on( 'clone', '.rwmb-image_upload, .rwmb-plupload_image', init )
} );
