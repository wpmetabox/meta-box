window.rwmb = window.rwmb || {};

jQuery( function ( $ )
{
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
					template : wp.template( 'rwmb-video-item' ),
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

	/**
	 * Initialize image fields
	 * @return void
	 */
	function initVideoField()
	{
		new VideoField( { input: this, el: $( this ).siblings( 'div.rwmb-media-view' ) } );
	}
	$( ':input.rwmb-video' ).each( initVideoField );
	$( '.rwmb-input' )
		.on( 'clone', ':input.rwmb-video', initVideoField )
} );
