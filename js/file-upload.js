window.rwmb = window.rwmb || {};

jQuery( function ( $ ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		MediaField = views.MediaField,
		FileUploadField, UploadButton;

	FileUploadField = views.FileUploadField = MediaField.extend( {
		createAddButton: function () {
			this.addButton = new UploadButton( {controller: this.controller} );
		}
	} );

	UploadButton = views.UploadButton = Backbone.View.extend( {
		className: 'rwmb-upload-area',
		tagName: 'div',
		template: wp.template( 'rwmb-upload-area' ),
		render: function () {
			this.$el.html( this.template( {} ) );
			return this;
		},

		initialize: function ( options ) {
			this.controller = options.controller;
			this.el.id = _.uniqueId( 'rwmb-upload-area-' );
			this.render();

			//Areas
			this.dropzone = this.el;
			this.browser = this.$( '.rwmb-browse-button' )[0];

			if ( wp.Uploader.browser.supported ) {
				this.initUploader();
			}

			// Auto hide if you reach the max number of media
			this.listenTo( this.controller, 'change:full', function () {
				this.$el.toggle( ! this.controller.get( 'full' ) );
			} );
		},

		//Initializes plupload
		//Uses code from wp.Uploader
		initUploader: function () {
			var isIE = navigator.userAgent.indexOf( 'Trident/' ) != - 1 || navigator.userAgent.indexOf( 'MSIE ' ) != - 1,
				self = this,
				extensions = this.getExtensions().join( ',' ),
				max_file_size;
			this.plupload = $.extend( true, {
				multipart_params: {},
				multipart: true,
				urlstream_upload: true,
				drop_element: this.dropzone,
				browse_button: this.browser,
				filters: {}
			}, wp.Uploader.defaults );

			if( max_file_size = this.controller.get( 'maxFileSize' ) ) {
				this.plupload.filters.max_file_size = max_file_size;
			}

			if ( extensions ) {
				this.plupload.filters.mime_types = [{title: i18nRwmbMedia.select, extensions: extensions}];
			}

			// Make sure flash sends cookies (seems in IE it does without switching to urlstream mode)
			if ( ! isIE && 'flash' === plupload.predictRuntime( this.plupload ) &&
			     ( ! this.plupload.required_features || ! this.plupload.required_features.hasOwnProperty( 'send_binary_string' ) ) ) {
				this.plupload.required_features = this.plupload.required_features || {};
				this.plupload.required_features.send_binary_string = true;
			}

			// Initialize the plupload instance.
			this.uploader = new plupload.Uploader( this.plupload );
			this.uploader.init();

			this.uploader.bind( 'FilesAdded', function ( up, files ) {
				_.each( files, function ( file ) {
					var attributes, image;

					// Ignore failed uploads.
					if ( plupload.FAILED === file.status ) {
						return;
					}

					// Generate attributes for a new `Attachment` model.
					attributes = _.extend( {
						file: file,
						uploading: true,
						date: new Date(),
						filename: file.name,
						menuOrder: 0,
						uploadedTo: wp.media.model.settings.post.id,
						icon: i18nRwmbMedia.loadingUrl
					}, _.pick( file, 'loaded', 'size', 'percent' ) );

					// Handle early mime type scanning for images.
					image = /(?:jpe?g|png|gif)$/i.exec( file.name );

					// For images set the model's type and subtype attributes.
					if ( image ) {
						attributes.type = 'image';

						// `jpeg`, `png` and `gif` are valid subtypes.
						// `jpg` is not, so map it to `jpeg`.
						attributes.subtype = ( 'jpg' === image[0] ) ? 'jpeg' : image[0];
					}

					// Create a model for the attachment, and add it to the Upload queue collection
					// so listeners to the upload queue can track and display upload progress.
					file.attachment = wp.media.model.Attachment.create( attributes );
					wp.Uploader.queue.add( file.attachment );
					self.controller.get( 'items' ).add( [file.attachment] );
				} );

				up.refresh();
				up.start();
			} );

			this.uploader.bind( 'UploadProgress', function ( up, file ) {
				file.attachment.set( _.pick( file, 'loaded', 'percent' ) );
			} );

			this.uploader.bind( 'FileUploaded', function ( up, file, response ) {
				var complete;

				try {
					response = JSON.parse( response.response );
				} catch ( e ) {
					return false;
				}

				if ( ! _.isObject( response ) || _.isUndefined( response.success ) || ! response.success ) {
					return false;
				}

				_.each( ['file', 'loaded', 'size', 'percent'], function ( key ) {
					file.attachment.unset( key );
				} );

				file.attachment.set( _.extend( response.data, {uploading: false} ) );
				wp.media.model.Attachment.get( response.data.id, file.attachment );

				complete = wp.Uploader.queue.all( function ( attachment ) {
					return ! attachment.get( 'uploading' );
				} );

				if ( complete ) {
					wp.Uploader.queue.reset();
				}
			} );

			this.uploader.bind( 'Error', function ( up, error ) {
				if ( error.file.attachment ) {
					error.file.attachment.destroy();
				}
			} );
		},

		getExtensions: function () {
			var mimeTypes = this.controller.get( 'mimeType' ).split( ',' ),
				exts = [];

			_.each( mimeTypes, function ( current, index ) {
				if ( i18nRwmbMedia.extensions[current] ) {
					exts = exts.concat( i18nRwmbMedia.extensions[current] );
				}
			} );
			return exts;
		}
	} );

	/**
	 * Initialize fields
	 * @return void
	 */
	function init() {
		new FileUploadField( {input: this, el: $( this ).siblings( 'div.rwmb-media-view' )} );
	}

	$( ':input.rwmb-file_upload' ).each( init );
	$( '.rwmb-input' )
		.on( 'clone', ':input.rwmb-file_upload', init )
} );
