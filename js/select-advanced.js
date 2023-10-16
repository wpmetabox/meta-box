( function ( $, rwmb ) {
	'use strict';

	// Cache ajax requests: https://github.com/select2/select2/issues/110#issuecomment-419247158
	var cache = {};

	/**
	 * Reorder selected values in correct order that they were selected.
	 * @param $select2 jQuery element of the select2.
	 */
	function reorderSelected( $select2 ) {
		var selected = $select2.data( 'selected' );
		if ( !selected ) {
			return;
		}
		selected.forEach( function ( value ) {
			var option = $select2.children( '[value="' + value + '"]' );
			option.detach();
			$select2.append( option );
		} );
		$select2.trigger( 'change' );
	}

	/**
	 * Transform select fields into beautiful dropdown with select2 library.
	 */
	function transform() {
		var $this = $( this ),
			options = $this.data( 'options' );

		$this.removeClass( 'select2-hidden-accessible' ).removeAttr( 'data-select2-id' );
		$this.siblings( '.select2-container' ).remove();
		$this.find( 'option' ).removeAttr( 'data-select2-id' );

		if ( options.ajax_data ) {
			options.ajax.dataType = 'json';
			options.ajax.data = function ( params ) {
				return Object.assign( options.ajax_data, params );
			};
			options.ajax.processResults = function ( response ) {
				var items = response.data.items.map( function ( item ) {
					return {
						id: item.value,
						text: _.unescape( item.label ),
					};
				} );

				var results = {
					results: items
				};
				if ( response.data.hasOwnProperty( 'more' ) ) {
					results.pagination = { more: true };
				}

				return results;
			};

			options.ajax.transport = function ( params, success, failure ) {
				if ( params.data._type === 'query' ) {
					delete params.data.page;
				}

				// Create cache key from ajax params from only neccessary keys to make cache available for multiple fields.
				var data = $.extend( true, {}, params.data );
				delete data.field.id;
				delete data.action;
				if ( !data.term ) {
					delete data.term;
				}

				var key = JSON.stringify( data );
				if ( cache[ key ] ) {
					success( cache[ key ] );
					return;
				}

				var actions = {
					'post': 'rwmb_get_posts',
					'taxonomy': 'rwmb_get_terms',
					'taxonomy_advanced': 'rwmb_get_terms',
					'user': 'rwmb_get_users'
				};
				params.data.action = actions[ params.data.field.type ];
				params.method = 'POST';

				return $.ajax( params ).then( function ( data ) {
					cache[ key ] = data;
					return data;
				} ).then( success ).fail( failure );
			};
		}

		$this.show();

		if ( $this.hasClass( 'rwmb-icon' ) ) {
			// Initialize select2 with icons for icon field.
			$this.trigger( 'init_icon_field', [ options ] );
		} else {
			// Initialize select2 normally.
			$this.select2( options );
		}

		if ( !$this.attr( 'multiple' ) ) {
			return;
		}

		reorderSelected( $this );

		/**
		 * Preserve the order that options are selected.
		 * @see https://github.com/select2/select2/issues/3106#issuecomment-255492815
		 */
		$this.on( 'select2:select', function ( event ) {
			var option = $this.children( '[value="' + event.params.data.id + '"]' );
			option.detach();
			$this.append( option ).trigger( 'change' );
		} );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-select_advanced, .rwmb-icon' ).each( transform );
	}

	function fixDropdownPosition( e ) {
		if ( $( "#wpadminbar" ).length === 0 ) {
			return;
		}

		if ( rwmbSelect2.isAdmin == 1 ) {
			$( 'body > .select2-container--open .select2-dropdown--above' ).css( 'top', 0 );
			return;
		}

		$( 'body > .select2-container:last-child > .select2-dropdown' ).css( 'top', $( document.body ).offset().top );
	};

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-select_advanced, .rwmb-icon', transform )
		.on( 'select2:open', fixDropdownPosition );
} )( jQuery, rwmb );
