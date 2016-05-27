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
			maxFiles   : 0,
			ids        : [],
			mimeType   : '',
			forceDelete: false,
			showStatus : true,
			length     : 0
		},

		//Initialize Controller model
		initialize: function ( options )
		{
			var that = this;
			// All numbers, no 0 ids
			this.set( 'ids', _.without( _.map( this.get( 'ids' ), Number ), 0, -1 ) );

			// Create items collection
			this.set( 'items', new wp.media.model.Attachments() );

			this.listenTo( this.get( 'items' ), 'add remove reset', function ()
			{
				var items = this.get( 'items' ),
					length = items.length,
					max = this.get( 'maxFiles' );

				this.set( 'length', length );
				this.set( 'full', max > 0 && length >= max );
			} );

			// Listen for destroy event on controller, delete all models when triggered
			this.on( 'destroy', function ( e )
			{
				if ( this.get( 'forceDelete' ) )
				{
					this.get( 'items' ).each( function ( item )
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
				this.get( 'items' ).props.set( {
					query  : true,
					include: this.get( 'ids' ),
					orderby: 'post__in',
					order  : 'ASC',
					type   : this.get( 'mimeType' ),
					perPage: this.get( 'maxFiles' ) || -1
				} );
				// Get more then trigger ready
				this.get( 'items' ).more();
			}
		},

		// Method to remove media items
		removeItem: function ( item )
		{
			this.get( 'items' ).remove( item );
			if ( this.get( 'forceDelete' ) )
				item.destroy();
		},

		// Method to add items
		addItems: function ( items )
		{
			if ( this.get( 'maxFiles' ) )
			{
				var left = this.get( 'maxFiles' ) - this.get( 'items' ).length;
				if ( left <= 0 )
					return this;

				items = _.difference( items, this.get( 'items' ).models );
				items = _.first( items, left );
			}
			this.get( 'items' ).add( items );
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
				{
					fieldName: this.$input.attr( 'name' ),
					ids      : this.$input.val().split( ',' )
				},
				this.$el.data()
			) );

			// Create views
			this.createList();
			this.createAddButton();
			this.createStatus();

			// Render
			this.render();

			// Load media
			this.controller.load();

			// Listen for destroy event on input
			this.$input.on( 'remove', function ()
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
	 * Media List
	 * lists media
	 */
	MediaList = views.MediaList = Backbone.View.extend( {
		tagName  : 'ul',
		className: 'rwmb-media-list',

		//Add item view
		addItemView: function ( item )
		{
			var view = this._views[item.cid] = new this.itemView( {
				model     : item,
				controller: this.controller
			} );

			this.$el.append( view.el );
		},

		//Remove item view
		removeItemView: function ( item )
		{
			if ( this._views[item.cid] )
			{
				this._views[item.cid].remove();
				delete this._views[item.cid];
			}
		},

		initialize: function ( options )
		{
			this._views = {};
			this.controller = options.controller;
			this.itemView = options.itemView || MediaItem;

			this.setEvents();

			// Sort media using sortable
			this.initSortable();
		},

		setEvents: function ()
		{
			this.listenTo( this.controller.get( 'items' ), 'add', this.addItemView );
			this.listenTo( this.controller.get( 'items' ), 'remove', this.removeItemView );
		},

		initSortable: function ()
		{
			var collection = this.controller.get( 'items' );
			this.$el.sortable( {
				// Change the position of the attachment as soon as the
				// mouse pointer overlaps a thumbnail.
				tolerance: 'pointer',

				// Record the initial `index` of the dragged model.
				start: function ( event, ui )
				{
					ui.item.data( 'sortableIndexStart', ui.item.index() );
				},

				// Update the model's index in the collection.
				// Do so silently, as the view is already accurate.
				update: function ( event, ui )
				{
					var model = collection.at( ui.item.data( 'sortableIndexStart' ) );

					// Silently shift the model to its new index.
					collection.remove( model, {
						silent: true
					} );
					collection.add( model, {
						silent: true,
						at    : ui.item.index()
					} );

					// Fire the `reset` event to ensure other collections sync.
					collection.trigger( 'reset', collection );
				}
			} );
		}
	} );

	/***
	 * MediaStatus
	 * Tracks status of media field if maxStatus is greater than 0
	 */
	MediaStatus = views.MediaStatus = Backbone.View.extend( {
		tagName  : 'span',
		className: 'rwmb-media-status',
		template : wp.template( 'rwmb-media-status' ),

		//Initialize
		initialize: function ( options )
		{
			this.controller = options.controller;

			//Auto hide if showStatus is false
			if ( !this.controller.get( 'showStatus' ) )
				this.$el.hide();

			//Rerender if changes happen in controller
			this.listenTo( this.controller, 'change:length', this.render );

			//Render
			this.render();
		},

		render: function ()
		{
			var attrs = _.clone( this.controller.attributes );
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
					title    : i18nRwmbMedia.select,
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
		render   : function ()
		{
			this.$el.text( i18nRwmbMedia.add );
			return this;
		},

		initialize: function ( options )
		{
			this.controller = options.controller;

			// Auto hide if you reach the max number of media
			this.listenTo( this.controller, 'change:full', function ()
			{
				this.$el.toggle( !this.controller.get( 'full' ) );
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
			this.listenTo( this.model, 'change', function ()
			{
				this.render();
			} );

			this.$el.data( 'id', this.model.cid );
		},


		events: {
			// Event when remove button clicked
			'click .rwmb-remove-media': function ( e )
			{
				this.controller.removeItem( this.model );
				return false;
			},

			'click .rwmb-edit-media': function ( e )
			{
				// Destroy the previous collection frame.
				if ( this._frame )
				{
					//this.stopListening( this._frame );
					this._frame.dispose();
				}

				// Trigger the media frame to open the correct item
				this._frame = wp.media( {
					frame     : 'edit-attachments',
					controller: {
						// Needed to trick Edit modal to think there is a gridRouter
						gridRouter: {
							navigate: function ( destination )
							{
							},
							baseUrl : function ( url )
							{
							}
						}
					},
					library   : this.controller.get( 'items' ),
					model     : this.model
				} );

				this._frame.open();

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
