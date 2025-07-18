( function ( $, rwmb ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		MediaField = views.MediaField,
		MediaItem = views.MediaItem,
		MediaList = views.MediaList,
		VideoField;

	VideoField = views.VideoField = MediaField.extend( {
		createList: function ()
		{
			this.list = new MediaList( {
				controller: this.controller,
				itemView: MediaItem.extend( {
					className: 'rwmb-video-item',
					template: rwmb.template( `
						<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="rwmb-media-input">
						<# if( _.indexOf( i18nRwmbVideo.extensions, data.url.substr( data.url.lastIndexOf('.') + 1 ) ) > -1 ) { #>
							<video controls="controls" class="rwmb-video-element" preload="metadata"
								<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
								<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
								<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
								<source type="{{ data.mime }}" src="{{ data.url }}"/>
							</video>
						<# } else { #>
							<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
								<img src="{{ data.image.src }}" />
							<# } else { #>
								<img src="{{ data.icon }}" />
							<# } #>
						<# } #>
						<div class="rwmb-media-info">
							<a href="{{{ data.url }}}" class="rwmb-file-title" target="_blank">
								<# if( data.title ) { #>
									{{{ data.title }}}
								<# } else { #>
									{{{ i18nRwmbMedia.noTitle }}}
								<# } #>
							</a>
							<div class="rwmb-file-name">{{{ data.filename }}}</div>
							<div class="rwmb-media-actions">
								<a class="rwmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
									{{{ i18nRwmbMedia.edit }}}
								</a>
								<a href="#" class="rwmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
									{{{ i18nRwmbMedia.remove }}}
								</a>
							</div>
						</div>
					` ),
					render: function()
					{
						var settings =  ! _.isUndefined( window._wpmejsSettings ) ? _.clone( _wpmejsSettings ) : {};
						MediaItem.prototype.render.apply( this, arguments );
						this.player = new MediaElementPlayer( this.$( 'video' ).get(0), settings );
					}
				} )
			} );
		}
	} );

	function initVideoField() {
		var $this = $( this ),
			view = new VideoField( { input: this } );
		$this.siblings( '.rwmb-media-view' ).remove();
		$this.after( view.el );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-video' ).each( initVideoField );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-video', initVideoField );

	wp?.hooks?.addAction( 'mb_ready', 'meta-box/ready/video', ref => {
		init( { target: ref } );
	} );
} )( jQuery, rwmb );
