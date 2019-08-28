( function ( $, rwmb ) {
	'use strict';

	// Object holds all methods related to fields' index when clone
	var cloneIndex = {
		/**
		 * Set index for fields in a .rwmb-clone
		 * @param $inputs .rwmb-clone element
		 * @param index Index value
		 */
		set: function ( $inputs, index ) {
			$inputs.each( function () {
				var $field = $( this );

				// Name attribute
				var name = this.name;
				if ( name && ! $field.closest( '.rwmb-group-clone' ).length ) {
					$field.attr( 'name', cloneIndex.replace( index, name, '[', ']', false ) );
				}

				// ID attribute
				var id = this.id;
				if ( id ) {
					$field.attr( 'id', cloneIndex.replace( index, id, '_', '', true, true ) );
				}

				$field.trigger( 'update_index', index );
			} );

			// Address button's value attribute
			var $address = $inputs.filter( '.rwmb-map-goto-address-button' );
			if ( $address.length ) {
				var value = $address.attr( 'value' );
				$address.attr( 'value', cloneIndex.replace( index, value, '_' ) );
			}
		},

		/**
		 * Replace an attribute of a field with updated index
		 * @param index New index value
		 * @param value Attribute value
		 * @param before String before returned value
		 * @param after String after returned value
		 * @param alternative Check if attribute does not contain any integer, will reset the attribute?
		 * @param isEnd Check if we find string at the end?
		 * @return string
		 */
		replace: function ( index, value, before, after, alternative, isEnd ) {
			before = before || '';
			after = after || '';

			if ( typeof alternative === 'undefined' ) {
				alternative = true;
			}

			var end = isEnd ? '$' : '';

			var regex = new RegExp( cloneIndex.escapeRegex( before ) + '(\\d+)' + cloneIndex.escapeRegex( after ) + end ),
				newValue = before + index + after;

			return regex.test( value ) ? value.replace( regex, newValue ) : (alternative ? value + newValue : value );
		},

		/**
		 * Helper function to escape string in regular expression
		 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions
		 * @param string
		 * @return string
		 */
		escapeRegex: function ( string ) {
			return string.replace( /[.*+?^${}()|[\]\\]/g, "\\$&" );
		},

		/**
		 * Helper function to create next index for clones
		 * @param $container .rwmb-input container
		 * @return integer
		 */
		nextIndex: function ( $container ) {
			var nextIndex = $container.data( 'next-index' );
			$container.data( 'next-index', nextIndex + 1 );
			return nextIndex;
		}
	};

	// Object holds all method related to fields' value when clone.
	var cloneValue = {
		/**
		 * Reset field value when clone. Expect this = current input.
		 */
		reset: function() {
			cloneValue.$field = $( this );
			cloneValue.type = cloneValue.$field.attr( 'type' );
			cloneValue.isHiddenField = cloneValue.$field.hasClass( 'rwmb-hidden' );

			if ( true === cloneValue.$field.data( 'clone-default' ) ) {
				cloneValue.resetToDefault();
			} else {
				cloneValue.clear();
			}
		},
		/**
		 * Reset field value to its default.
		 */
		resetToDefault: function() {
			var defaultValue = cloneValue.$field.data( 'default' );
			if ( 'radio' === cloneValue.type ) {
				cloneValue.$field.prop( 'checked', cloneValue.$field.val() === defaultValue );
			} else if ( 'checkbox' === cloneValue.type ) {
				cloneValue.$field.prop( 'checked', !!defaultValue );
			} else if ( 'select' === cloneValue.type ) {
				cloneValue.$field.find( 'option[value="' + defaultValue + '"]' ).prop( 'selected', true );
			} else if ( ! cloneValue.isHiddenField ) {
				cloneValue.$field.val( defaultValue );
			}
		},
		/**
		 * Clear field value.
		 */
		clear: function() {
			if ( 'radio' === cloneValue.type || 'checkbox' === cloneValue.type ) {
				cloneValue.$field.prop( 'checked', false );
			} else if ( 'select' === cloneValue.type ) {
				cloneValue.$field.prop( 'selectedIndex', - 1 );
			} else if ( ! cloneValue.isHiddenField ) {
				cloneValue.$field.val( '' );
			}
		}
	};

	/**
	 * Clone fields
	 * @param $container A div container which has all fields
	 */
	function clone( $container ) {
		var $last = $container.children( '.rwmb-clone' ).last(),
			$clone = $last.clone(),
			nextIndex = cloneIndex.nextIndex( $container );

		// Reset value for fields
		var $inputs = $clone.find( rwmb.inputSelectors );
		$inputs.each( cloneValue.reset );

		// Insert Clone
		$clone.insertAfter( $last );

		// Trigger custom event for the clone instance. Required for Group extension to update sub fields.
		$clone.trigger( 'clone_instance', nextIndex );

		// Set fields index. Must run before trigger clone event.
		cloneIndex.set( $inputs, nextIndex );

		// Trigger custom clone event.
		$inputs.trigger( 'clone', nextIndex );

		// After cloning fields.
		$inputs.trigger( 'after_clone', nextIndex );

		// Trigger custom change event for MB Blocks to update block attributes.
		$inputs.first().trigger( 'mb_change' );
	}

	/**
	 * Hide remove buttons when there's only 1 of them
	 *
	 * @param $container .rwmb-input container
	 */
	function toggleRemoveButtons( $container ) {
		var $clones = $container.children( '.rwmb-clone' );
		$clones.children( '.remove-clone' ).toggle( $clones.length > 1 );

		// Recursive for nested groups.
		$container.find( '.rwmb-input' ).each( function () {
			toggleRemoveButtons( $( this ) );
		} );
	}

	/**
	 * Toggle add button
	 * Used with [data-max-clone] attribute. When max clone is reached, the add button is hid and vice versa
	 *
	 * @param $container .rwmb-input container
	 */
	function toggleAddButton( $container ) {
		var $button = $container.children( '.add-clone' ),
			maxClone = parseInt( $container.data( 'max-clone' ) ),
			numClone = $container.children( '.rwmb-clone' ).length;

		$button.toggle( isNaN( maxClone ) || ( maxClone && numClone < maxClone ) );
	}

	function addClone( e ) {
		e.preventDefault();

		var $container = $( this ).closest( '.rwmb-input' );
		clone( $container );

		toggleRemoveButtons( $container );
		toggleAddButton( $container );
		sortClones.apply( $container[0] );
	}

	function removeClone( e ) {
		e.preventDefault();

		var $this = $( this ),
			$container = $this.closest( '.rwmb-input' );

		// Remove clone only if there are 2 or more of them
		if ( $container.children( '.rwmb-clone' ).length < 2 ) {
			return;
		}

		$this.parent().trigger( 'remove' ).remove();
		toggleRemoveButtons( $container );
		toggleAddButton( $container );

		// Trigger custom change event for MB Blocks to update block attributes.
		$container.find( rwmb.inputSelectors ).first().trigger( 'mb_change' );
	}

	/**
	 * Sort clones.
	 * Expect this = .rwmb-input element.
	 */
	function sortClones() {
		var $container = $( this );

		if ( undefined !== $container.sortable( 'instance' ) ) {
			return;
		}
		if ( 0 === $container.children( '.rwmb-clone' ).length ) {
			return;
		}

		$container.sortable( {
			handle: '.rwmb-clone-icon',
			placeholder: ' rwmb-clone rwmb-sortable-placeholder',
			items: '> .rwmb-clone',
			start: function ( event, ui ) {
				// Make the placeholder has the same height as dragged item
				ui.placeholder.height( ui.item.outerHeight() );

				// Fixed WYSIWYG field blank when inside a sortable, cloneable group.
				// https://stackoverflow.com/a/25667486/371240
				ui.item.find( '.rwmb-wysiwyg' ).each( function () {
					tinymce.execCommand( 'mceRemoveEditor', false, this.id );
				} );
			},
			update: function( event, ui ) {
				ui.item.find( '.rwmb-wysiwyg' ).each( function () {
					tinymce.execCommand( 'mceAddEditor', true, this.id );
				} );

				ui.item.find( rwmb.inputSelectors ).first().trigger( 'mb_change' );
			}
		} );
	}

	function start() {
		var $container = $( this );
		toggleRemoveButtons( $container );
		toggleAddButton( $container );

		$container.data( 'next-index', $container.children( '.rwmb-clone' ).length );
		sortClones.apply( this );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-input' ).each( start );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'click', '.add-clone', addClone )
		.on( 'click', '.remove-clone', removeClone );
} )( jQuery, rwmb );
