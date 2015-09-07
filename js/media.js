window.rwmb = window.rwmb || {};

jQuery( function ( $ )
{
	'use strict';
	
	var  views = rwmb.views = rwmb.views || {},
		MediaList, MediaItem;
		
	MediaList = views.MediaList = Backbone.View.extend( {
		template: wp.template('rwmb-media'),
		itemViews: {},
		events: {
			'click .rwmb-add-media' : function(){
				this.frame();
				return false;	
			}
		},
		initialize: function( options ){
			this.name = this.$el.data('name') || options.name;
			this.values = this.$el.data('values') || options.values;
			this.type = this.$el.data('mime-type') || options.type;
			this.multiple = this.$el.data('multiple');
			//this.multiple = 'undefined' === typeof this.multiple ? false : this.multiple; 
			//Collection
			this.collection = new wp.media.model.Attachments();			
			
			this.listenTo( this.collection, 'add', function( model, collection, options ){
				var item = this.itemViews[model.cid] =  new MediaItem({ model: model, collection: collection, name: this.name }) ;
				this.$( '.rwmb-media-list' ).append(item.el);
				
				if( ! this.multiple ) {
					if( this.collection.length > 1 )
						this.collection.reset( [model], options );
					this.$( '.rwmb-add-media' ).hide();
				}
			} );
			
			this.listenTo( this.collection, 'remove', function( model, collection, options ){
				this.itemViews[model.cid].remove();
				delete this.itemViews[model.cid];
				if( ! this.multiple && this.collection.length < 1 )
					this.$( '.rwmb-add-media' ).show();
			} );
			
			this.listenTo( this.collection, 'reset', function( collection, options ){
				_.each( this.itemViews, function( item ){
					item.remove();
				}, this );
				delete this.itemViews;
				this.itemViews = {};
			} );
			
			this.$( '.rwmb-media-list' ).sortable();
			
			if( ! _.isEmpty( this.values) ) {
				 this.collection.props.set( {					
					query: true, 
					include: this.values, 
					orderby: 'post__in',
					order: 'ASC', 
					type: this.type,
					perPage:  this.multiple ? -1 : 1 
				} );
				this.collection.more();	
			}				
		},
		
		frame: function() {
			var ids = this.collection.pluck('id');
			console.log(this.multiple);
			// Destroy the previous collection frame.
			if ( this._frame ) {
				this.stopListening( this._frame );
				this._frame.dispose();
			}
			
			this._frame = wp.media( {
				className: 'media-frame rwmb-media-frame',
				multiple : this.multiple,
				title    : 'Select Media',
				library  : {
					type: this.type,
					exclude: ids
				}
			} );
			
			this.listenTo( this._frame, 'select', function(){
				var selection = this._frame.state().get( 'selection' );
				selection.each( function( item ){
					this.collection.add( item );
				}, this );
			} );
			
			this._frame.open();			
		}
	} );
	
	MediaItem = views.MediaItem = Backbone.View.extend( {
		tagName: 'li',
		template: wp.template( 'rwmb-media-item' ),
		initialize: function( options ) {
			this.name = options.name;
			this.render();	
		}, 
		
		events: {
			'click .rwmb-remove-media': function(e) {
				this.collection.remove( this.model );
				this.remove();
				return false;	
			}
		}, 
		
		render: function() {
			var attrs = _.clone( this.model.attributes );
			this.$el.html( this.template( {name: this.name, attachment: attrs } ) );
			return this;	
		}
	} );
	
	$( '.rwmb-media' ).each( function( index ){
		new MediaList( { el: this } );
	} );
} );