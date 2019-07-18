( function ( $, wp, _, rwmb, i18n ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		models = rwmb.models = rwmb.models || {},
		media = wp.media,
		MediaFrame = media.view.MediaFrame,
		MediaCollection, Controller, MediaField, MediaList, MediaItem, MediaButton, MediaStatus, EditMedia,
		MediaDetails, MediaLibrary, MediaSelect;

	MediaCollection = Backbone.Collection.extend( {
		model: wp.media.model.Attachment,

		initialize: function ( models, options ) {
			this.controller = options.controller || new models.Controller;
			this.on( 'add remove reset', function () {
				var max = this.controller.get( 'maxFiles' );
				this.controller.set( 'length', this.length );
				this.controller.set( 'full', max > 0 && this.length >= max );
			} );
		},

		add: function ( models, options ) {
			var max = this.controller.get( 'maxFiles' ),
				left = max - this.length;

			if ( ! models || ( max > 0 && left <= 0 ) ) {
				return this;
			}
			if ( ! models.hasOwnProperty( 'length' ) ) {
				models = [models];
			} else if ( models instanceof media.model.Attachments ) {
				models = models.models;
			}

			models = _.difference( models, this.models );
			if ( left > 0 ) {
				models = _.first( models, left );
			}

			Backbone.Collection.prototype.add.call( this, models, options );
		},

		remove: function ( models, options ) {
			// Don't remove models if event is not fired from MB plugin.
			if( ! $( event.target ).closest( '.rwmb-field, [data-class="rwmb-field"]' ).length ) {
				return;
			}
			models = Backbone.Collection.prototype.remove.call( this, models, options );
			if ( this.controller.get( 'forceDelete' ) === true ) {
				models = ! _.isArray( models ) ? [models] : models;
				_.each( models, function ( model ) {
					model.destroy();
				} );
			}
		},

		destroyAll: function () {
			_.each( _.clone( this.models ), function ( model ) {
				model.destroy();
			} );
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
		initialize: function () {
			// All numbers, no 0 ids
			this.set( 'ids', _.without( _.map( this.get( 'ids' ), Number ), 0, - 1 ) );

			// Create items collection
			this.set( 'items', new MediaCollection( [], {controller: this} ) );

			// Listen for destroy event on controller, delete all models when triggered
			this.on( 'destroy', function () {
				if ( this.get( 'forceDelete' ) ) {
					this.get( 'items' ).destroyAll();
				}
			} );
		}
	} );

	/***
	 * MediaField
	 * Sets up media field view and subviews
	 */
	MediaField = views.MediaField = Backbone.View.extend( {
		className: 'rwmb-media-view',
		initialize: function ( options ) {
			var that = this,
				fieldName = options.input.name;
			this.$input = $( options.input );

			if ( 1 != this.$input.attr( 'data-single-image' ) ) {
				fieldName += '[]';
			}

			this.controller = new Controller( _.extend(
				{
					fieldName: fieldName,
					ids: this.$input.val().split( ',' )
				},
				this.$input.data( 'options' )
			) );

			// Create views
			this.createList();
			this.createAddButton();
			this.createStatus();

			this.render();
			this.loadInitialAttachments();

			// Listen for destroy event on input
			this.$input.on( 'remove', function () {
				that.controller.destroy();
			} );

			this.$input.on( 'media:reset', function() {
				that.controller.get( 'items' ).reset();
			} );

			this.controller.get( 'items' ).on( 'add remove reset', _.debounce( function () {
				that.$input.trigger( 'change', [that.$( '.rwmb-media-input' )] );
			}, 500 ) );

			this.controller.get( 'items' ).on( 'remove', _.debounce( function () {
				that.$input.val( '' );
			}, 500 ) );
		},

		loadInitialAttachments: function () {
			if ( ! this.$input.val() ) {
				return;
			}
			var models = this.$input.data( 'attachments' ).map( function( attachment ) {
				return wp.media.model.Attachment.create( attachment );
			} );
			this.controller.get( 'items' ).add( models );
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

		initialize: function ( options ) {
			this.controller = options.controller;
			this.collection = this.controller.get( 'items' );
			this.itemView = options.itemView || MediaItem;
			this.getItemView = _.memoize( function ( item ) {
					var itemView = new this.itemView( {
						model: item,
						controller: this.controller
					} );

					this.listenToItemView( itemView );

					return itemView;
				},
				function ( item ) {
					return item.cid;
				}
			);

			this.listenTo( this.collection, 'add', this.addItemView );
			this.listenTo( this.collection, 'remove', this.removeItemView );
			this.listenTo( this.collection, 'reset', this.resetItemViews );

			// Sort items using helper 'clone' to prevent trigger click on the image, which means reselect.
			this.$el.sortable( {
				helper : 'clone'
			} );
		},

		listenToItemView: function ( itemView ) {
			this.listenTo( itemView, 'click:remove', this.removeItem );
			this.listenTo( itemView, 'click:switch', this.switchItem );
			this.listenTo( itemView, 'click:edit', this.editItem );
		},

		addItemView: function ( item ) {
			var index = this.collection.indexOf( item ),
				itemEl = this.getItemView( item ).el,
				$children = this.$el.children();

			if ( 0 >= index ) {
				this.$el.prepend( itemEl );
			} else if ( $children.length <= index ) {
				this.$el.append( itemEl )
			} else {
				$children.eq( index - 1 ).after( itemEl );
			}
		},

		// Remove item view
		removeItemView: function ( item ) {
			this.getItemView( item ).$el.detach();
		},

		removeItem: function ( item ) {
			this.collection.remove( item );
		},

		resetItemViews: function( items ){
			var that = this;
			_.each( that.models, that.removeItemView );
			items.each( that.addItemView );
		},

		switchItem: function ( item ) {
			if ( this._switchFrame ) {
				this._switchFrame.dispose();
			}
			this._switchFrame = new MediaSelect( {
				multiple: false,
				editing: true,
				library: {
					type: this.controller.get( 'mimeType' )
				},
				edit: this.collection
			} );

			// Refresh content when frame opens
			this._switchFrame.on( 'open', function() {
				var frameContent = this._switchFrame.content.get();
				if ( frameContent && frameContent.collection ) {
					frameContent.collection.mirroring._hasMore = true;
					frameContent.collection.more();
				}
			}, this );

			this._switchFrame.on( 'select', function () {
				var selection = this._switchFrame.state().get( 'selection' ),
					collection = this.collection,
					index = collection.indexOf( item );

				if ( ! _.isEmpty( selection ) ) {
					collection.remove( item );
					collection.add( selection, {at: index} );
				}
			}, this );

			this._switchFrame.open();
			return false;
		},

		editItem: function ( item ) {
			if ( this._editFrame ) {
				this._editFrame.dispose();
			}

			// Trigger the media frame to open the correct item.
			this._editFrame = new EditMedia( {
				frame: 'edit-attachments',
				controller: {
					// Needed to trick Edit modal to think there is a gridRouter.
					gridRouter: {
						navigate: function ( destination ) {
						},
						baseUrl: function ( url ) {
						}
					}
				},
				library: this.collection,
				model: item
			} );

			this._editFrame.open();
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
			this.listenTo( this.controller.get( 'items' ), 'reset', this.render );

			// Render
			this.render();
		},

		render: function () {
			this.$el.html( this.template( this.controller.toJSON() ) );
		}
	} );

	/***
	 * Media Button
	 * Selects and adds media to controller
	 */
	MediaButton = views.MediaButton = Backbone.View.extend( {
		tagName: 'div',
		className: 'rwmb-media-add',
		template: wp.template( 'rwmb-media-button' ),
		events: {
			'click .button': function () {
				if ( this._frame ) {
					this._frame.dispose();
				}
				var maxFiles = this.controller.get( 'maxFiles' );
				this._frame = new MediaSelect( {
					multiple: maxFiles > 1 || maxFiles <= 0 ? 'add' : false,
					editing: true,
					library: {
						type: this.controller.get( 'mimeType' )
					},
					edit: this.collection
				} );

				// Refresh content when frame opens
				this._frame.on( 'open', function() {
					var frameContent = this._frame.content.get();
					if ( frameContent && frameContent.collection ) {
						frameContent.collection.mirroring._hasMore = true;
						frameContent.collection.more();
					}
				}, this );

				this._frame.on( 'select', function () {
					var selection = this._frame.state().get( 'selection' );
					this.collection.add( selection.models );
				}, this );

				this._frame.open();
			}
		},
		render: function () {
			this.$el.html( this.template( {text: i18n.add} ) );
			return this;
		},

		initialize: function ( options ) {
			this.controller = options.controller;
			this.collection = this.controller.get( 'items' );

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
		className: 'rwmb-media-item attachment',
		template: wp.template( 'rwmb-media-item' ),
		initialize: function ( options ) {
			this.controller = options.controller;
			this.collection = this.controller.get( 'items' );
			this.render();
			this.listenTo( this.model, 'change', this.render );

			this.$el.data( 'id', this.model.cid );
		},

		events: {
			'click .rwmb-image-overlay': function () {
				this.trigger( 'click:switch', this.model );
				return false;
			},

			// Event when remove button clicked
			'click .rwmb-remove-media': function () {
				this.trigger( 'click:remove', this.model );
				return false;
			},

			'click .rwmb-edit-media': function () {
				this.trigger( 'click:edit', this.model );
				return false;
			}
		},

		render: function () {
			var data = this.model.toJSON();
			data.controller = this.controller.toJSON();
			this.$el.html( this.template( data ) );
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
	MediaDetails = views.MediaDetails = media.view.Attachment.Details.TwoColumn.extend( {
		render: function () {
			var that = this;
			media.view.Attachment.Details.prototype.render.apply( this, arguments );
			this.players = this.players || [];

			media.mixin.unsetPlayers.call( this );

			this.$( 'audio, video' ).each( function ( i, elem ) {
				var el = media.view.MediaDetails.prepareSrc( elem );
				that.players.push( new window.MediaElementPlayer( el, media.mixin.mejsSettings ) );
			} );
		}
	} );

	/**
	 * MediaLibrary
	 * Custom version of Library to exclude already selected media in a media frame
	 */
	MediaLibrary = media.controller.Library.extend( {
		defaults: _.defaults( {
			multiple: 'add',
			filterable: 'all',
			priority: 100,
			syncSelection: false
		}, media.controller.Library.prototype.defaults ),

		activate: function () {
			var library = this.get( 'library' ),
				edit = this.frame.options.edit;

			if ( this.editLibrary && this.editLibrary !== edit ) {
				library.unobserve( this.editLibrary );
			}

			// Accepts attachments that exist in the original library and
			// that do not exist in gallery's library.
			library.validator = function ( attachment ) {
				return ! ! this.mirroring.get( attachment.cid ) && ! edit.get( attachment.cid ) && media.model.Selection.prototype.validator.apply( this, arguments );
			};

			// Reset the library to ensure that all attachments are re-added
			// to the collection. Do so silently, as calling `observe` will
			// trigger the `reset` event.
			library.reset( library.mirroring.models, {silent: true} );
			library.observe( edit );
			this.editLibrary = edit;

			media.controller.Library.prototype.activate.apply( this, arguments );
		}
	} );

	/**
	 * MediaSelect
	 * Custom version of Select media frame that uses  MediaLibrary
	 */
	MediaSelect = views.MediaSelect = MediaFrame.Select.extend( {
		/**
		 * Create the default states on the frame.
		 */
		createStates: function () {
			var options = this.options;

			// Add reference so we know MediaFrame belongs to MB plugin.
			this.$el.attr( 'data-class', 'rwmb-field' );

			if ( this.options.states ) {
				return;
			}

			// Add the default states.
			this.states.add( [
				// Main states.
				new MediaLibrary( {
					library: media.query( options.library ),
					multiple: options.multiple,
					priority: 20
				} )
			] );
		}
	} );

	/***
	 * EditMedia
	 * Custom version of EditAttachments frame to prevent all video and audio from being unset
	 */
	EditMedia = views.EditMedia = MediaFrame.EditAttachments.extend( {
		/**
		 * Content region rendering callback for the `edit-metadata` mode.
		 *
		 * @param {Object} contentRegion Basic object with a `view` property, which
		 *                               should be set with the proper region view.
		 */
		editMetadataMode: function ( contentRegion ) {
			contentRegion.view = new MediaDetails( {
				controller: this,
				model: this.model
			} );

			/**
			 * Attach a subview to display fields added via the
			 * `attachment_fields_to_edit` filter.
			 */
			contentRegion.view.views.set( '.attachment-compat', new media.view.AttachmentCompat( {
				controller: this,
				model: this.model
			} ) );
		}
	} );

	function initMediaField() {
		var $this = $( this ),
			view = $this.data( 'view' );

		if ( view ) {
			return;
		}

		view = new MediaField( { input: this } );

		$this.siblings( '.rwmb-media-view' ).remove();
		$this.after( view.el );
		$this.data( 'view', view );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-file_advanced' ).each( initMediaField );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-file_advanced', initMediaField );
} )( jQuery, wp, _, rwmb, i18nRwmbMedia );
