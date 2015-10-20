window.rwmb = window.rwmb || {};

jQuery( function ( $ )
{
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		MediaField,MediaList, MediaItem, ImageField, ImageList, ImageItem, MediaButton, UploadButton;
	
	MediaList = views.MediaList = Backbone.View.extend( {
		tagName: 'ul',
		className: 'rwmb-media-list',
		createItemView: function( options ){
			return new MediaItem( options );
		},
		
		addItemView: function( item ){
			if ( this.props.maxFiles > 0 && this.collection.length > this.props.maxFiles )
			{
				this.collection.pop( item );
			}
			else
			{
				this.itemViews[item.cid] = this.createItemView( { model: item, collection: this.collection } );
				this.$el.append( this.itemViews[item.cid].el );	
			}			
		},
		
		render: function(){
			this.$el.empty();
			this.collection.each( this.addItemView );
		},
		
		initialize: function( options ) {
			var that = this;
			this.itemViews = {};
			this.props = options.props;
			this.listenTo( this.collection, 'add', this.addItemView );

			this.listenTo( this.collection, 'remove', function( item, collection ) {
				if ( this.itemViews[item.cid] )
				{
					this.itemViews[item.cid].remove();
					delete this.itemViews[item.cid];
				}
			} );

			this.$el.sortable( {
				stop : function ( event, ui )
				{
					that.$el.children().each( function ()
					{
						var cid = $( this ).data( 'cid' );

						if ( cid )
						{
							var model = that.collection.get( cid );
							if ( model )
							{
								that.collection.remove( model );
								that.collection.add( model );
							}
						}
					} );
				},
				delay: 150
			} );
			
			this.render();
		}
	} );
	
	ImageList = views.ImageList = MediaList.extend( {
		createItemView: function( options ){
			return new ImageItem( options );
		},
	} );
	
	MediaField = views.MediaField = Backbone.View.extend( {
		events    : {
			'destroy' : function(){
				if( this.forceDelete ) {
					_.each( _.clone( this.collection.models ), function( model ) {
						model.destroy();
					});
				}
			}
		},
		
		createMediaList: function()
		{
			return new MediaList( { collection: this.collection, props: this.props } );
		},
		
		createNewMediaButton: function()
		{
			return new MediaButton( { collection: this.collection, props: this.props } );
		},
		
		initialize: function ( options )
		{
			var that = this;
			this.input = $( options.input );
			this.values = this.input.val().split( ',' );
			this.props = this.$el.data();
			this.createMediaList = options.createMediaList || this.createMediaList;
			
			//Collection
			this.collection = new wp.media.model.Attachments();
			this.render();
			
			this.listenTo( this.collection, 'add remove reset', _.debounce( function ()
			{
				var ids = this.collection.pluck( 'id' );
				this.input.val( ids.join( ',' ) );
			}, 500 ) );
			
			this.listenTo( this.collection, 'remove', function( model ) {
				if( this.props.forceDelete ) {
					model.destroy();	
				}
			} );

			if ( !_.isEmpty( this.values ) )
			{
				this.collection.props.set( {
					query  : true,
					include: this.values,
					orderby: 'post__in',
					order  : 'ASC',
					type   : this.props.mimeType,
					perPage: this.props.maxFiles || -1
				} );
				this.collection.more();
			}
		},
		
		render: function() {
			this.$el.empty();
			this.$el.append( this.createMediaList().el );
			this.$el.append( this.createNewMediaButton().el );
		}
	} );
	
	ImageField = views.ImageField = MediaField.extend( {
		createMediaList: function(){
			return new ImageList( { collection: this.collection, props: this.props } );
		} 
	} );
	
	MediaButton = views.MediaButton = Backbone.View.extend( {
		className: 'rwmb-add-media button',
		tagName: 'a',
		template: wp.template( 'rwmb-add-media' ),
		events: 
		{
			'click': function(){
				var ids = this.collection.pluck( 'id' );
				// Destroy the previous collection frame.
				if ( this._frame )
				{
					this.stopListening( this._frame );
					this._frame.dispose();
				}
	
				this._frame = wp.media( {
					className: 'media-frame rwmb-media-frame',
					multiple : true,
					title    : 'Select Media',
					library  : {
						type   : this.props.mimeType,
						exclude: ids
					}
				} );
	
				this.listenTo( this._frame, 'select', function ()
				{
					var selection = this._frame.state().get( 'selection' );
					selection.each( function ( item )
					{
						this.collection.add( item );
					}, this );
				} );
	
				this._frame.open();
			},
		}, 
		render: function()
		{
			this.$el.html( this.template( {} ) );
			return this;
		},
		
		initialize:  function( options ) 
		{
			this.props = options.props;
			this.listenTo( this.collection, 'add remove reset', function(){
				if ( this.props.maxFiles > 0 && this.collection.length >= this.props.maxFiles )
				{
					this.$el.hide();
				}
				else
				{
					this.$el.show();
				}
			} );
			
			this.render();
		}
	} );
	
	UploadButton = views.UploadButton = Backbone.View.extend( {
		initialize:  function( options ) 
		{
			this.props = options.props;
			this.listenTo( this.collection, 'add remove reset', function(){
				if ( this.props.maxFiles > 0 && this.collection.length >= this.props.maxFiles )
				{
					this.$el.hide();
				}
				else
				{
					this.$el.show();
				}
			} );
		}
	} );
	
	MediaItem = views.MediaItem = Backbone.View.extend( {
		tagName   : 'li',
		className: 'rwmb-media-item',
		template  : wp.template( 'rwmb-media-item' ),
		initialize: function ( options )
		{
			this.render();
			this.$el.data( 'cid', this.model.cid );
			this.listenTo( this.model, 'destroy', function( model ) {
				this.collection.remove( this.model );
			} );
		},

		events: {
			'click .rwmb-remove-media': function ( e )
			{
				this.collection.remove( this.model );
				return false;
			}
		},

		render: function ()
		{
			var attrs = _.clone( this.model.attributes );
			this.$el.html( this.template( attrs ) );
			return this;
		}
	} );
	
	ImageItem = views.ImageItem = MediaItem.extend( {
		className: 'rwmb-image-item',
		template  : wp.template( 'rwmb-image-item' ),
	} );

	/**
	 * Initialize media fields
	 * @return void
	 */
	function initMediaField()
	{
		new MediaField( { input: this, el: $( this ).siblings( 'div.rwmb-media-view' )} );
	}
	
	function initImageField()
	{
		new ImageField( { input: this, el: $( this ).siblings( 'div.rwmb-media-view' )} );
	}

	$( ':input.rwmb-media' ).each( initMediaField );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-media', initMediaField );
	$( '.rwmb-input' ).on( 'remove', '.rwmb-media-clone', function(){
		$( this ).find( 'div.rwmb-media-view' ).trigger( 'destroy' );
	} );
	
	$( ':input.rwmb-image-advanced' ).each( initImageField );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-image-advanced', initImageField );
	$( '.rwmb-input' ).on( 'remove', '.rwmb-image_advanced-clone', function(){
		$( this ).find( 'div.rwmb-media-view' ).trigger( 'destroy' );
	} );
} );
