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
		var view = new VideoField( { input: this } );
		//Remove old then add new
		$( this ).siblings( 'div.rwmb-media-view' ).remove();
		$( this ).after( view.el );
	}
	$( '.rwmb-video' ).each( initVideoField );
	$( document )
		.on( 'clone', '.rwmb-video', initVideoField )
} );
