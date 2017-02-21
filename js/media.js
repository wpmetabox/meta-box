window.rwmb = window.rwmb || {};

jQuery( function ( $ ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		models = rwmb.models = rwmb.models || {},
		MediaCollection, Controller, MediaField, MediaList, MediaItem, MediaButton, MediaStatus, EditMedia, MediaDetails;

	MediaCollection = models.MediaCollection = wp.media.model.Attachments.extend( {
		initialize: function( models, options ) {
			this.controller = options.controller || new models.Controller;
			this.on( 'add remove reset', function () {
				var max = this.controller.get( 'maxFiles' );
				this.controller.set( 'length', this.length );
				this.controller.set( 'full', max > 0 && this.length >= max );
			} );

			wp.media.model.Attachments.prototype.initialize.call( this, models, options );
		},

		add: function( models, options ) {
			var max = this.controller.get( 'maxFiles' ),
				left = max - this.length;

			if ( max > 0 && left <= 0 ) {
				return this;
			}

			if( !models.hasOwnProperty( 'length' ) ){
				models = [models];
			}
			else if( models instanceof wp.media.model.Attachments ) {
				models = models.models;
			}

			if( left > 0 ) {
				models = _.difference( models, this.models );
				models = _.first( models, left );
			}

			return wp.media.model.Attachments.prototype.add.call( this, models, options );
		},

		remove: function( models, options ) {
			var models = wp.media.model.Attachments.prototype.remove.call( this, models, options );
			if ( this.controller.get( 'forceDelete' ) === true ) {
				models = !_.isArray( models ) ? [models] : models;
				_.each( models, function( model ) {
				  model.destroy();
				});
			}
		},

		destroyAll: function() {
			_.each( _.clone( this.models), function( model ) {
			  model.destroy();
			});
		}
	} );

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
			maxStatus: true,
			length: 0
		},

		//Initialize Controller model
		initialize: function ( options ) {
			var that = this;
			// All numbers, no 0 ids
			this.set( 'ids', _.without( _.map( this.get( 'ids' ), Number ), 0, - 1 ) );

			// Create items collection
			this.set( 'items', new MediaCollection( [], { controller: this } ) );

			// Listen for destroy event on controller, delete all models when triggered
			this.on( 'destroy', function ( e ) {
				if ( this.get( 'forceDelete' ) ) {
					this.get( 'items' ).destroyAll();
				}
			} );
		},


		// Method to load media
		load: function () {
			var that = this;
			// Load initial media
			if ( ! _.isEmpty( this.get( 'ids' ) ) ) {
				this.get( 'items' ).props.set( {
					query: true,
					include: this.get( 'ids' ),
					orderby: 'post__in',
					order: 'ASC',
					type: this.get( 'mimeType' ),
					perPage: this.get( 'maxFiles' ) || - 1
				} );
				// Get more then trigger ready
				this.get( 'items' ).more();
			}
		}
	} );

	/***
	 * MediaField
	 * Sets up media field view and subviews
	 */
	MediaField = views.MediaField = Backbone.View.extend( {
		initialize: function ( options ) {
			var that = this;
			this.$input = $( options.input );
			this.controller = new Controller( _.extend(
				{
					fieldName: this.$input.attr( 'name' ),
					ids: this.$input.val().split( ',' )
				},
				this.$el.data( 'options' )
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
			this.$input.on( 'remove', function () {
				this.controller.destroy();
			} );

			this.controller.get( 'items' ).on( 'add remove reset', _.debounce( function ( e ) {
				that.$input.trigger( 'change' );
			}, 500 ) );
		},

		// Creates media list
		createList: function () {
			this.list = new MediaList( {controller: this.controller} );
		},

		// Creates button that adds media
		createAddButton: function () {
			this.addButton = new MediaButton( {controller: this.controller} );
		},

		// Creates status
		createStatus: function () {
			this.status = new MediaStatus( {controller: this.controller} );
		},

		// Render field and adds sub fields
		render: function () {
			// Empty then add parts
			this.$el.empty().append(
				this.list.el,
				this.status.el,
				this.addButton.el
			);
		}
	} );

	/***
	 * Media List
	 * lists media
	 */
	MediaList = views.MediaList = Backbone.View.extend( {
		tagName: 'ul',
		className: 'rwmb-media-list',

		//Add item view
		addItemView: function ( item ) {
			var index = this.controller.get( 'items' ).indexOf( item ),
				itemEl = this.getItemView( item ).el;

			if( 0 >= index ) {
				this.$el.prepend( itemEl );
			}
			else if( this.$el.children().length <= index ) {
				this.$el.append( itemEl )
			}
			else {
				this.$el.children().eq( index - 1 ).after( itemEl );
			}
		},

		//Remove item view
		removeItemView: function ( item ) {
			this.getItemView(item).$el.detach();
		},

		initialize: function ( options ) {
			this.controller   = options.controller;
			this.itemView     = options.itemView || MediaItem;
			this.getItemView  = _.memoize(
	 			function( item ) {
	 				return new this.itemView( {
	 					model: item,
	 					controller: this.controller
	 				} );
	 			},
	 			function( item ) {
	 				return item.cid;
	 			} );

			this.listenTo( this.controller.get( 'items' ), 'add', this.addItemView );
			this.listenTo( this.controller.get( 'items' ), 'remove', this.removeItemView );

			// Sort media using sortable
			this.initSortable();
		},

		initSortable: function () {
			var collection = this.controller.get( 'items' );
			this.$el.sortable( {
				// Change the position of the attachment as soon as the
				// mouse pointer overlaps a thumbnail.
				tolerance: 'pointer',

				// Record the initial `index` of the dragged model.
				start: function ( event, ui ) {
					ui.item.data( 'sortableIndexStart', ui.item.index() );
				},

				// Update the model's index in the collection.
				// Do so silently, as the view is already accurate.
				update: function ( event, ui ) {
					var model = collection.at( ui.item.data( 'sortableIndexStart' ) );

					// Silently shift the model to its new index.
					collection.remove( model, {
						silent: true
					} );
					collection.add( model, {
						silent: true,
						at: ui.item.index()
					} );

					// Fire the `reset` event to ensure other collections sync.
					collection.trigger( 'reset', collection );
				}
			} );
		}
	} );

	/***
	 * MediaStatus view.
	 * Show number of selected/uploaded files and number of files remain if "maxStatus" parameter is true.
	 */
	MediaStatus = views.MediaStatus = Backbone.View.extend( {
		tagName: 'div',
		className: 'rwmb-media-status',
		template: wp.template( 'rwmb-media-status' ),

		initialize: function ( options ) {
			this.controller = options.controller;

			// Auto hide if maxStatus is false
			if ( ! this.controller.get( 'maxStatus' ) ) {
				this.$el.hide();
				return;
			}

			// Re-render if changes happen in controller
			this.listenTo( this.controller.get( 'items' ), 'update', this.render );

			// Render
			this.render();
		},

		render: function () {
			var attributes = _.clone( this.controller.attributes );
			this.$el.html( this.template( attributes ) );
		}
	} );

	/***
	 * Media Button
	 * Selects and adds ,edia to controller
	 */
	MediaButton = views.MediaButton = Backbone.View.extend( {
		tagName: 'div',
		className: 'rwmb-media-add',
		template: wp.template( 'rwmb-media-button' ),
		events: {
			'click .button': function () {
				// Destroy the previous collection frame.
				if ( this._frame ) {
					//this.stopListening( this._frame );
					this._frame.dispose();
				}

				this._frame = wp.media( {
					className: 'media-frame rwmb-media-frame',
					multiple: true,
					title: i18nRwmbMedia.select,
					editing: true,
					library: {
						type: this.controller.get( 'mimeType' )
					}
				} );

				this._frame.on( 'select', function () {
					var selection = this._frame.state().get( 'selection' );
					this.controller.get( 'items' ).add( selection.models );
				}, this );

				this._frame.open();
			}
		},
		render: function () {
			var attrs = { text: i18nRwmbMedia.add }
			this.$el.html( this.template( attrs ) );
			return this;
		},

		initialize: function ( options ) {
			this.controller = options.controller;

			// Auto hide if you reach the max number of media
			this.listenTo( this.controller, 'change:full', function () {
				this.$el.toggle( ! this.controller.get( 'full' ) );
			} );

			this.render();
		}
	} );

	/***
	 * MediaItem
	 * View for individual media items
	 */
	MediaItem = views.MediaItem = Backbone.View.extend( {
		tagName: 'li',
		className: 'rwmb-media-item',
		template: wp.template( 'rwmb-media-item' ),
		initialize: function ( options ) {
			this.controller = options.controller;
			this.render();
			this.listenTo( this.model, 'change', function () {
				this.render();
			} );

			this.$el.data( 'id', this.model.cid );
		},


		events: {
			'click .rwmb-switch': function( e ) {
				if ( this._frame ) {
					//this.stopListening( this._frame );
					this._frame.dispose();
				}
				this._frame = wp.media( {
					className: 'media-frame rwmb-media-frame',
					multiple: false,
					title: i18nRwmbMedia.select,
					editing: true,
					library: {
						type: this.controller.get( 'mimeType' )
					}
				} );

				this._frame.on( 'select', function () {
					var selection = this._frame.state().get( 'selection' ),
						collection = this.controller.get( 'items' ),
						index = collection.indexOf( this.model );
					if( !_.isEmpty( selection ) ) {
						collection.remove( this.model );
						collection.add( selection, { at: index } );
					}
				}, this );

				this._frame.open();
				return false;
			},

			// Event when remove button clicked
			'click .rwmb-remove-media': function ( e ) {
				this.controller.get( 'items' ).remove( this.model );
				return false;
			},

			'click .rwmb-edit-media': function ( e ) {
				// Destroy the previous collection frame.
				if ( this._frame ) {
					//this.stopListening( this._frame );
					this._frame.dispose();
				}

				// Trigger the media frame to open the correct item
				this._frame = new EditMedia( {
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

		render: function () {
			var attrs = _.clone( this.model.attributes );
			attrs.fieldName = this.controller.get( 'fieldName' );
			this.$el.html( this.template( attrs ) );
			return this;
		}
	} );

	/**
	 * Extend media frames to make things work right
	 */

	 /**
	  * MediaDetails
	  * Custom version of TwoColumn view to prevent all video and audio from being unset
	  */
	 MediaDetails = views.MediaDetails = wp.media.view.Attachment.Details.TwoColumn.extend( {
		 render: function() {
			 var that = this;
			 wp.media.view.Attachment.Details.prototype.render.apply( this, arguments );
			 this.players = this.players || [];

			 wp.media.mixin.unsetPlayers.call( this );

			 this.$( 'audio, video' ).each( function (i, elem) {
				 var el = wp.media.view.MediaDetails.prepareSrc( elem );
				 that.players.push( new window.MediaElementPlayer( el, wp.media.mixin.mejsSettings ) );
			 } );
		 }
	 } );

	 /***
		* EditMedia
		* Custom version of EditAttachments frame to prevent all video and audio from being unset
		*/
	 EditMedia = views.EditMedia = wp.media.view.MediaFrame.EditAttachments.extend( {
		 /**
			* Content region rendering callback for the `edit-metadata` mode.
			*
			* @param {Object} contentRegion Basic object with a `view` property, which
			*                               should be set with the proper region view.
			*/
		 editMetadataMode: function( contentRegion ) {
			 contentRegion.view = new MediaDetails({
				 controller: this,
				 model:      this.model
			 });

			 /**
				* Attach a subview to display fields added via the
				* `attachment_fields_to_edit` filter.
				*/
			 contentRegion.view.views.set( '.attachment-compat', new wp.media.view.AttachmentCompat({
				 controller: this,
				 model:      this.model
			 }) );
		 },
	 } );

	/**
	 * Initialize media fields
	 * @return void
	 */
	function initMediaField() {
		new MediaField( {input: this, el: $( this ).siblings( 'div.rwmb-media-view' )} );
	}


	$( ':input.rwmb-file_advanced' ).each( initMediaField );
	$( '.rwmb-input' )
		.on( 'clone', ':input.rwmb-file_advanced', initMediaField );
} );
