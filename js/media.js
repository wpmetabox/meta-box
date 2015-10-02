window.rwmb = window.rwmb || {};

jQuery( function ( $ )
{
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		MediaList, MediaItem;

	MediaList = views.MediaList = Backbone.View.extend( {
		template  : wp.template( 'rwmb-media-list' ),
		itemViews : {},
		events    : {
			'click .rwmb-add-media': function ()
			{
				this.frame();
				return false;
			}
		},
		initialize: function ( options )
		{
			var that = this;
			this.input = $( options.input );
			this.values = this.input.val().split( ',' );
			this.type = this.$el.data( 'mime-type' ) || options.type;
			this.max = this.$el.data( 'max-files' ) || options.maxFiles;
			//Collection
			this.collection = new wp.media.model.Attachments();

			this.render();

			this.listenTo( this.collection, 'add', function ( model, collection, options )
			{
				if ( this.max > 0 && this.collection.length > this.max )
				{
					this.collection.pop( model );
				}
				else
				{
					var item = this.itemViews[model.cid] = new MediaItem( { model: model, collection: collection } );
					this.$( '.rwmb-media-list' ).append( item.el );
				}
				this.updateButton();
				this.updateInput();
			} );

			this.listenTo( this.collection, 'remove', function ( model, collection, options )
			{
				if ( this.itemViews[model.cid] )
				{
					this.itemViews[model.cid].remove();
					delete this.itemViews[model.cid];
				}

				this.updateInput();
				this.updateButton();
			} );

			this.$( '.rwmb-media-list' ).sortable( {
				stop : function ( event, ui )
				{
					that.$( '.rwmb-media-list' ).children().each( function ()
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

			if ( !_.isEmpty( this.values ) )
			{
				this.collection.props.set( {
					query  : true,
					include: this.values,
					orderby: 'post__in',
					order  : 'ASC',
					type   : this.type,
					perPage: this.max || -1
				} );
				this.collection.more();
			}
		},

		frame: function ()
		{
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
					type   : this.type,
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

		render: function ()
		{
			this.$el.html( this.template( {} ) );
			return this;
		},

		updateButton: function ()
		{
			if ( this.max > 0 && this.collection.length >= this.max )
			{
				this.$( '.rwmb-add-media' ).hide();
			}
			else
			{
				this.$( '.rwmb-add-media' ).show();
			}
		},

		updateInput: _.debounce( function ()
		{
			var ids = this.collection.pluck( 'id' );
			this.input.val( ids.join( ',' ) );
		}, 500 )
	} );

	MediaItem = views.MediaItem = Backbone.View.extend( {
		tagName   : 'li',
		template  : wp.template( 'rwmb-media-item' ),
		initialize: function ( options )
		{
			this.render();
			this.$el.data( 'cid', this.model.cid );
			this.$el.addClass( this.model.get( 'type' ) );
		},

		events: {
			'click .rwmb-remove-media': function ( e )
			{
				this.collection.remove( this.model );
				this.remove();
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

	/**
	 * Initialize media fields
	 * @return void
	 */
	function initMediaField()
	{
		new MediaList( { input: this, el: $( this ).siblings( 'div.rwmb-media-view' ) } );
	}

	$( ':input.rwmb-media' ).each( initMediaField );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-media', initMediaField );
} );
