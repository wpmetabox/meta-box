window.rwmb = window.rwmb || {};

jQuery( function ( $ )
{
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		models = rwmb.models = rwmb.models || {},
		Controller, MediaField, MediaList, MediaItem, MediaButton, MediaStatus;

	/***
	 * Controller Model
	 * Manages data of media field and media models.  Most of the media views will use this to manage the media
	 */
	Controller = models.Controller = Backbone.Model.extend( {
		//Default options
		defaults: {
			maxFiles: 0,
			ids: [],
			mimeType: '',
			forceDelete: false,
			showStatus: true
		},

		//Initialize Controller model
		initialize: function ( options )
		{
			var that = this;
			// All numbers, no 0 ids
			this.set( 'ids', _.without( _.map( this.get( 'ids' ), Number ), 0 ) );

			// Create items collection
			this.items = new wp.media.model.Attachments();

			// Listen to when media is added to collection
			this.listenTo( this.items, 'add', function ( item )
			{
				// Limit max files
				var maxFiles = this.get( 'maxFiles' );
				if ( maxFiles > 0 && this.items.length > maxFiles )
				{
					this.items.remove( item );
				}
			} );

			// Listen for destroy event on controller, delete all models when triggered
			this.on( 'destroy', function ( e )
			{
				if( this.get( 'forceDelete' ) )
				{
					this.items.each( function ( item )
					{
						item.destroy();
					} );
				}
			} );
		},


		// Method to load media
		load: function ()
		{
			var that = this;
			// Load initial media
			if ( !_.isEmpty( this.get( 'ids' ) ) )
			{
				this.items.props.set( {
					query  : true,
					include: this.get( 'ids' ),
					orderby: 'post__in',
					order  : 'ASC',
					type   : this.get( 'mimeType' ),
					perPage: this.get( 'maxFiles' ) || -1
				} );
				// Get more then trigger ready
				this.items.more().done( function() {
					that.trigger( 'ready' );
				} );
			}
			else
			{
				// No initial media so ready
				that.trigger( 'ready' );
			}
		},

		// Method to remove media items
		removeItem: function( item )
		{
			this.items.remove( item );
			if ( this.get( 'forceDelete' ) )
			{
				item.destroy();
			}
		},

		// Method to add items
		addItems: function ( items )
		{
			this.items.add( items );
		}
	} );

	/***
	 * Media List
	 * lists media
	 */
	MediaList = views.MediaList = Backbone.View.extend( {
		tagName    : 'ul',
		className  : 'rwmb-media-list',

		//Add item view
		addItemView: function ( item )
		{
			if ( ! this.itemViews[item.cid] )
			{
				this.itemViews[item.cid] = new this.itemView( {
					model     : item,
					controller: this.controller
				} );
			}
			this.$el.append( this.itemViews[item.cid].el );
		},

		//Remove item view
		removeItemView: function ( item )
		{
			if ( this.itemViews[item.cid] )
			{
				this.itemViews[item.cid].remove();
				delete this.itemViews[item.cid];
			}
		},

		initialize: function ( options )
		{
			this.itemViews = {};
			this.controller = options.controller;
			this.itemView = options.itemView || MediaItem;

			this.listenTo( this.controller.items, 'add', this.addItemView );

			this.listenTo( this.controller.items, 'remove', this.removeItemView );

			// Sort media using sortable
			this.initSort();
		},

		initSort: function ()
		{
			this.$el.sortable( { delay: 150 } );
		}
	} );

	/***
	 * MediaField
	 * Sets up media field view and subviews
	 */
	MediaField = views.MediaField = Backbone.View.extend( {
		initialize: function ( options )
		{
			var that = this;
			this.$input = $( options.input );
			this.controller = new Controller( _.extend(
				{},
				this.$el.data(),
				{
					fieldName: this.$input.attr( 'name' ) ,
					ids: this.$input.val().split( ',' )
				}
			) );

			this.$input.val( '' );

			// Create views
			this.createList();
			this.createAddButton()
			this.createStatus();

			// Render
			this.render();

			// Load media
			this.controller.load();

			// Listen for destroy event on input
			this.$input.on( 'remove', function()
			{
					this.controller.destroy();
			} )
		},

		// Creates media list
		createList: function ()
		{
			this.list = new MediaList( { controller: this.controller } );
		},

		// Creates button that adds media
		createAddButton: function ()
		{
			this.addButton = new MediaButton( { controller: this.controller } );
		},

		// Creates status
		createStatus: function ()
		{
			this.status = new MediaStatus( { controller: this.controller } );
		},

		// Render field and adds sub fields
		render: function ()
		{
			// Empty then add parts
			this.$el.empty().append(
				this.list.el,
				this.addButton.el,
				this.status.el
			);
		}
	} );

	/***
	 * MediaStatus
	 * Tracks status of media field if maxStatus is greater than 0
	 */
	MediaStatus = views.MediaStatus = Backbone.View.extend( {
		tagName   : 'span',
		className : 'rwmb-media-status',
		template  : wp.template( 'rwmb-media-status' ),

		//Initialize
		initialize: function ( options )
		{
			this.controller = options.controller;

			//Auto hide if showStatus is false
			if( ! this.controller.get( 'showStatus' ) )
				this.$el.hide();

			//Rerender if changes happen in controller
			this.listenTo( this.controller.items, 'add remove', this.render );

			//Render
			this.render();
		},

		render: function ()
		{
			var attrs = _.clone( this.controller.attributes );
			attrs.length = this.controller.items.length;
			this.$el.html( this.template( attrs ) );
		}
	} );

	/***
	 * Media Button
	 * Selects and adds ,edia to controller
	 */
	MediaButton = views.MediaButton = Backbone.View.extend( {
		className: 'rwmb-add-media button',
		tagName  : 'a',
		events   : {
			click: function ()
			{
				// Destroy the previous collection frame.
				if ( this._frame )
				{
					//this.stopListening( this._frame );
					this._frame.dispose();
				}

				this._frame = wp.media( {
					className: 'media-frame rwmb-media-frame',
					multiple : true,
					title    : 'Select Media',
					editing  : true,
					library  : {
						type: this.controller.get( 'mimeType' )
					}
				} );

				this._frame.on( 'select', function ()
				{
					var selection = this._frame.state().get( 'selection' );
					this.controller.addItems( selection.models );
				}, this );

				this._frame.open();
			}
		},
		render    : function ()
		{
			this.$el.text( i18nRwmbMedia.add );
			return this;
		},

		initialize: function ( options )
		{
			this.controller = options.controller;

			// Auto hide if you reach the max number of media
			this.listenTo( this.controller.items, 'add remove', function ()
			{
				var maxFiles = this.controller.get( 'maxFiles' );

				if ( maxFiles > 0 )
				{
					this.$el.toggle( this.controller.items.length < maxFiles );
				}
			} );

			this.render();
		}
	} );

	/***
	 * MediaItem
	 * View for individual media items
	 */
	MediaItem = views.MediaItem = Backbone.View.extend( {
		tagName   : 'li',
		className : 'rwmb-media-item',
		template  : wp.template( 'rwmb-media-item' ),
		initialize: function ( options )
		{
			this.controller = options.controller;
			this.render();
			this.listenTo( this.model, 'change', function()
			{
				this.render();
			} );
		},

		events: {
			// Event when remove button clicked
			'click .rwmb-remove-media': function ( e )
			{
				this.controller.removeItem( this.model );
				return false;
			}
		},

		render: function ()
		{
			var attrs = _.clone( this.model.attributes );
			attrs.fieldName = this.controller.get( 'fieldName' );
			this.$el.html( this.template( attrs ) );
			return this;
		}
	} );


	/**
	 * Initialize media fields
	 * @return void
	 */
	function initMediaField()
	{
		new MediaField( { input: this, el: $( this ).siblings( 'div.rwmb-media-view' ) } );
	}


	$( ':input.rwmb-file_advanced' ).each( initMediaField );
	$( '.rwmb-input' )
		.on( 'clone', ':input.rwmb-file_advanced', initMediaField );
} );
