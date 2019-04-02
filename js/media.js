/* global jQuery, _,i18nRwmbMedia */

window.rwmb = window.rwmb || {};

jQuery( function ( $ ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		models = rwmb.models = rwmb.models || {},
		media = wp.media,
		MediaFrame = media.view.MediaFrame,
		MediaCollection, Controller, MediaField, MediaList, MediaItem, MediaButton, MediaStatus, EditMedia,
		MediaDetails, MediaLibrary, MediaSelect;

	MediaCollection = models.MediaCollection = media.model.Attachments.extend( {
		initialize: function ( models, options ) {
			this.controller = options.controller || new models.Controller;
			this.on( 'add remove reset', function () {
				var max = this.controller.get( 'maxFiles' );
				this.controller.set( 'length', this.length );
				this.controller.set( 'full', max > 0 && this.length >= max );
			} );

			media.model.Attachments.prototype.initialize.call( this, models, options );
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

			/**
			 * Make a copy version of models. Do not work directly on models since WordPress might sent some events (like 'remove') to those models.
			 * @see https://metabox.io/support/topic/error-with-image-advanced-in-gutenberg/
			 * @see https://stackoverflow.com/a/5344074/371240
			 */
			models = JSON.parse( JSON.stringify( models ) );

			return media.model.Attachments.prototype.add.call( this, models, options );
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
		},

		// Load initial media
		load: function () {
			if ( _.isEmpty( this.get( 'ids' ) ) ) {
				return;
			}
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

			// Render
			this.render();

			// Load media
			this.controller.load();

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

					return itemView;
				},
				function ( item ) {
					return item.cid;
				}
			);

			this.listenTo( this.collection, 'add', this.addItemView );
			this.listenTo( this.collection, 'reset', this.resetItemViews );

			// Sort items using helper 'clone' to prevent trigger click on the image, which means reselect.
			this.$el.sortable( {
				helper : 'clone'
			} );
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

		resetItemViews: function( items ){
			var that = this;
			this.$el.empty();
			items.each( function( item ) {
				that.addItemView( item );
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

				this._frame.on( 'select', function () {
					var selection = this._frame.state().get( 'selection' );
					this.collection.add( selection.models );
				}, this );

				this._frame.open();
			}
		},
		render: function () {
			this.$el.html( this.template( {text: i18nRwmbMedia.add} ) );
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
			this.listenTo( this.model, 'remove', this.remove );

			this.$el.data( 'id', this.model.cid );
		},

		events: {
			'click .rwmb-image-overlay': 'replace',
			'click .rwmb-remove-media': 'delete',
			'click .rwmb-edit-media': 'edit',
		},

		replace: function() {
			if ( this._replaceFrame ) {
				this._replaceFrame.dispose();
			}
			this._replaceFrame = new MediaSelect( {
				multiple: false,
				editing: true,
				library: {
					type: this.controller.get( 'mimeType' )
				},
				edit: this.collection
			} );

			this._replaceFrame.on( 'select', function () {
				var selection = this._replaceFrame.state().get( 'selection' );

				if ( _.isEmpty( selection ) ) {
					return;
				}

				var index = this.collection.indexOf( this.model );
				// Remove from collection also triggers 'remove' event for the model, which calls this.remove() to remove the element from DOM.
				this.collection.remove( this.model );
				this.collection.add( selection, {at: index} );
			}, this );

			this._replaceFrame.open();

			return false;
		},

		edit: function() {
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
				model: this.model
			} );

			this._editFrame.open();

			return false;
		},

		delete: function() {
			// Remove from collection also triggers 'remove' event for the model, which calls this.remove() to remove the element from DOM.
			this.collection.remove( this.model );

			if ( true === this.controller.get( 'forceDelete' ) ) {
				this.model.destroy();
			}

			return false;
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

	/**
	 * Initialize media fields
	 * @return void
	 */
	function initMediaField() {
		var view = new MediaField( { input: this } );
		//Remove old then add new
		$( this ).siblings( 'div.rwmb-media-view' ).remove();
		$( this ).after( view.el );
	}

	$( '.rwmb-file_advanced' ).each( initMediaField );
	$( document ).on( 'clone', '.rwmb-file_advanced', initMediaField );
} );
