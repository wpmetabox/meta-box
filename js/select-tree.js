( function ( $, rwmb ) {
	'use strict';

	function setInitialRequiredProp() {
		var $this = $( this ),
			required = $this.prop( 'required' );

		if ( required ) {
			$this.data( 'initial-required', required );
		}
	}

	function unsetRequiredProp() {
		$( this ).prop( 'required', false );
	}

	function setRequiredProp() {
		var $this = $( this );

		if ( $this.data( 'initial-required' ) ) {
			$this.prop( 'required', true );
		}
	}

	function toggleTree() {
		var $this = $( this ),
			val = $this.val(),
			$tree = $this.siblings( '.rwmb-select-tree' ),
			$selected = $tree.filter( "[data-parent-id='" + val + "']" ),
			$notSelected = $tree.not( $selected );

		$selected.removeClass( 'hidden' ).find( 'select' ).each( setRequiredProp );
		$notSelected.addClass( 'hidden' ).find( 'select' ).each( unsetRequiredProp ).prop( 'selectedIndex', 0 );
	}

	function getSelect2Options( $select ) {
		var options = $select.data( 'options' ) || {};

		var showThumbnail = options.show_thumbnail
			|| ( options.ajax_data && options.ajax_data.field && options.ajax_data.field.show_thumbnail );

		if ( showThumbnail ) {
			options.escapeMarkup = function ( markup ) {
				return markup;
			};

			options.templateResult = function ( data ) {
				if ( ! data.id ) {
					return data.text;
				}

				var thumb = data.thumbnail || ( data.element ? $( data.element ).data( 'thumbnail' ) : '' );
				var img = thumb
					? '<img src="' + thumb + '" class="rwmb-post-thumbnail" width="20" height="20" alt="" />'
					: '<span class="rwmb-post-thumbnail rwmb-post-thumbnail--empty"></span>';

				return $( '<span class="rwmb-post-option">' + img + ' ' + data.text + '</span>' );
			};

			options.templateSelection = options.templateResult;
		}

		return options;
	}

	function instantiateSelect2() {
		var $this = $( this ),
			options = getSelect2Options( $this );

		$this
			.removeClass( 'select2-hidden-accessible' ).removeAttr( 'data-select2-id' )
			.children().removeAttr( 'data-select2-id' ).end()
			.siblings( '.select2-container' ).remove().end()
			.select2( options );

		toggleTree.call( this );
	}

	function init( e ) {
		var $select = $( e.target ).find( '.rwmb-select-tree > select' );

		$select.each( setInitialRequiredProp );
		$select.each( function () {
			var $this = $( this ),
				options = getSelect2Options( $this );

			$this.select2( options );
		} );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'change', '.rwmb-select-tree > select', toggleTree )
		.on( 'clone', '.rwmb-select-tree > select', instantiateSelect2 );
} )( jQuery, rwmb );
