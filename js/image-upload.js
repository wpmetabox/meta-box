( function ( $, rwmb ) {
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

	function initImageUpload() {
		var view = new ImageUploadField( { input: this } );
		//Remove old then add new
		$( this ).siblings( 'div.rwmb-media-view' ).remove();
		$( this ).after( view.el );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-image_upload, .rwmb-plupload_image' ).each( initImageUpload );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-image_upload, .rwmb-plupload_image', initImageUpload )
} )( jQuery, rwmb );
