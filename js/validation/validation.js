( function ( $, rwmb, i18n ) {
	'use strict';

	/**
	 * Extract the validation key from an input's name attribute. Usually it's the field ID, but sometimes (like for `file`), it's the field's input name.
	 *
	 * field[]    => field   // Fields with multiple values: file, checkbox list, etc.
	 * field[1]   => field   // Cloneable fields
	 * field[1][] => field   // Cloneable fields with multiple values: file, checkbox list, etc.
	 *
	 * group[field][]    => field  // Group with fields with multiple values: file, checkbox list, etc.
	 * group[field][1]   => field  // Group with cloneable fields
	 * group[field][1][] => field  // Group with cloneable fields with multiple values: file, checkbox list, etc.
	 *
	 * group[1][field][]    => field  // Cloneable group with fields with multiple values: file, checkbox list, etc.
	 * group[1][field][1]   => field  // Cloneable group with cloneable fields
	 * group[1][field][1][] => field  // Cloneable group with cloneable fields with multiple values: file, checkbox list, etc.
	 *
	 * group[subgroup][field][]    => field  // Subgroup with fields with multiple values: file, checkbox list, etc.
	 * group[subgroup][field][1]   => field  // Subgroup with cloneable fields
	 * group[subgroup][field][1][] => field  // Subgroup with cloneable fields with multiple values: file, checkbox list, etc.
	 *
	 * group[subgroup][1][field][]    => field  // Cloneable subgroup with fields with multiple values: file, checkbox list, etc.
	 * group[subgroup][1][field][1]   => field  // Cloneable subgroup with cloneable fields
	 * group[subgroup][1][field][1][] => field  // Cloneable subgroup with cloneable fields with multiple values: file, checkbox list, etc.
	 *
	 * group[1][subgroup][field][]    => field  // Cloneable group with subgroup with fields with multiple values: file, checkbox list, etc.
	 * group[1][subgroup][field][1]   => field  // Cloneable group with subgroup with cloneable fields
	 * group[1][subgroup][field][1][] => field  // Cloneable group with subgroup with cloneable fields with multiple values: file, checkbox list, etc.
	 *
	 * group[1][subgroup][1][field][]    => field  // Cloneable group with cloneable subgroup with fields with multiple values: file, checkbox list, etc.
	 * group[1][subgroup][1][field][1]   => field  // Cloneable group with cloneable subgroup with cloneable fields
	 * group[1][subgroup][1][field][1][] => field  // Cloneable group with cloneable subgroup with cloneable fields with multiple values: file, checkbox list, etc.
	 */
	const getValidationKey = name => {
		// Detect name parts in format of anything[] or anything[1].
		let parts = name.match( /^(.+?)(?:\[\d+\]|(?:\[\]))?$/ );

		if ( parts[ 1 ] && isNaN( parts[ 1 ] ) ) {
			// Remove []
			let words = name.match( /(\w+)|(\[\w+\])/g );
			let resultArray = [ words.join( "" ) ];

			// Remove characters "[" and "]".
			words.forEach( matchedValue => {
				if ( matchedValue.startsWith( "[" ) ) {
					resultArray.push( matchedValue.substring( 1, matchedValue.length - 1 ) );
				} else {
					resultArray.push( matchedValue );
				}
			} );

			parts[ 0 ] = resultArray[ 0 ];
			parts[ 1 ] = isNaN( resultArray[ resultArray.length - 1 ] ) ? resultArray[ resultArray.length - 1 ] : resultArray[ resultArray.length - 2 ];
		}

		return parts.pop();
	};

	/**
	 * Fix validation not working for cloneable files or fields in groups.
	 */
	$.validator.staticRules = function ( element ) {
		let rules = {},
			validator = $.data( element.form, "validator" );

		// No rules.
		if ( validator.settings.rules === null || Object.keys( validator.settings.rules ).length === 0 ) {
			return rules;
		}

		// Do not validate hidden fields.
		if ( element.type === 'hidden' ) {
			return rules;
		}

		let key = getValidationKey( element.name );

		/**
		 * Cloneable files or files in groups.
		 * Input name is transformed into format `_file_{unique_id}`
		 * There is also a hidden input with name `_index_{field_id}` with value `_file_{unique_id}`
		 *
		 * In this case, `key` is always `_file_{unique_id}`
		 *
		 * Note that for cloneable files, validation rule is set for `_index_{field_id}`. For files in groups, validation rule is still `{field_id}`.
		 */
		if ( element.type === 'file' && ( $( element ).closest( '.rwmb-clone' ).length > 0 || $( element ).closest( '.rwmb-group-wrapper' ).length > 0 ) ) {
			const $input = $( element ).closest( '.rwmb-input' );
			const $indexInput = $input.find( '*[value="' + key + '"]' );

			key = getValidationKey( $indexInput.attr( 'name' ) );

			// Remove prefix `_index_` from input name when in groups.
			if ( !validator.settings.rules[ key ] && key.includes( '_index_' ) ) {
				key = key.slice( 7 );
			}

			if ( validator.settings.rules[ key ] ) {
				// Set message for element.
				validator.settings.messages[ element.name ] = validator.settings.messages[ key ];
				// Set rule for element.
				return $.validator.normalizeRule( validator.settings.rules[ key ] ) || {};
			}

			return rules;
		}

		// For normal fields and fields in groups: set rules by their field IDs (validation keys).

		// Set message for element.
		validator.settings.messages[ element.name ] = validator.settings.messages[ key ];
		// Set rule for element.
		return $.validator.normalizeRule( validator.settings.rules[ key ] ) || {};
	};

	/**
	 * Make jQuery Validation works with multiple inputs with same names.
	 * Need for file, image fields where users can upload multiple files with same input names.
	 *
	 * @link https://stackoverflow.com/q/931687/371240
	 */
	$.validator.prototype.checkForm = function () {
		this.prepareForm();
		for ( var i = 0, elements = ( this.currentElements = this.elements() ); elements[ i ]; i++ ) {
			if ( this.findByName( elements[ i ].name ).length !== undefined && this.findByName( elements[ i ].name ).length > 1 ) {
				for ( var cnt = 0; cnt < this.findByName( elements[ i ].name ).length; cnt++ ) {
					this.check( this.findByName( elements[ i ].name )[ cnt ] );
				}
			} else {
				this.check( elements[ i ] );
			}
		}
		return this.valid();
	};

	class Validation {
		constructor( formSelector ) {
			this.$form = $( formSelector );
			this.validationElements = this.$form.find( '.rwmb-validation' );
			this.showAsterisks();
			this.getSettings();
		}

		init() {
			this.$form
				// Update underlying textarea before submit.
				// Don't use submitHandler() because form can be submitted via Ajax on the front end.
				.on( 'submit', function () {
					if ( typeof tinyMCE !== 'undefined' ) {
						tinyMCE.triggerSave();
					}
				} )
				.validate( this.settings );
		}

		showAsterisks() {
			this.validationElements.each( function () {
				const data = $( this ).data( 'validation' );

				$.each( data.rules, function ( k, v ) {
					if ( !v[ 'required' ] ) {
						return;
					}
					let $el = $( '[name="' + k + '"]' );
					if ( !$el.length ) {
						$el = $( '[name*="[' + k + ']"]' ); // Subfields in groups.
					}
					if ( $el.length ) {
						$el.closest( '.rwmb-input' ).siblings( '.rwmb-label' ).find( 'label' ).append( '<span class="rwmb-required">*</span>' );
					}
				} );
			} );
		}

		getSettings() {
			this.settings = {
				ignore: ':not(.rwmb-media,.rwmb-image_select,.rwmb-wysiwyg,.rwmb-color,.rwmb-map,.rwmb-osm,.rwmb-switch,[class|="rwmb"])',
				errorPlacement: function ( error, element ) {
					error.appendTo( element.closest( '.rwmb-input' ) );
				},
				errorClass: 'rwmb-error',
				errorElement: 'p',
				invalidHandler: this.invalidHandler.bind( this )
			};

			// Gather all validation rules.
			var that = this;
			this.validationElements.each( function () {
				$.extend( true, that.settings, $( this ).data( 'validation' ) );
			} );
		}

		invalidHandler() {
			this.showMessage();
			// Group field will automatically expand and show an error warning when collapsing
			for ( var i = 0; i < this.$form.data( 'validator' ).errorList.length; i++ ) {
				$( '#' + this.$form.data( 'validator' ).errorList[ i ].element.id ).closest( '.rwmb-group-collapsed' ).removeClass( 'rwmb-group-collapsed' );
			}
			// Custom event for showing error fields inside tabs/hidden divs. Use setTimeout() to run after error class is added to inputs.
			var that = this;
			setTimeout( function () {
				that.$form.trigger( 'after_validate' );
			}, 200 );
		}

		showMessage() {
			// Re-enable the submit ( publish/update ) button and hide the ajax indicator
			$( '#publish' ).removeClass( 'button-primary-disabled' );
			$( '#ajax-loading' ).attr( 'style', '' );
			$( '#rwmb-validation-message' ).remove();
			this.$form.before( '<div id="rwmb-validation-message" class="notice notice-error is-dismissible"><p>' + i18n.message + '</p></div>' );
		}
	};

	class GutenbergValidation extends Validation {
		init() {
			var that = this,
				editor = wp.data.dispatch( 'core/editor' ),
				savePost = editor.savePost; // Reference original method.

			if ( that.settings ) {
				that.$form.validate( that.settings );
			}

			// Change the editor method.
			editor.savePost = function ( options = {} ) {
				// Bypass the validation when previewing in Gutenberg.
				if ( typeof options === 'object' && options.isPreview ) {
					return savePost( options );
				}

				// Must call savePost() here instead of in submitHandler() because the form has inline onsubmit callback.
				if ( that.$form.valid() ) {
					return savePost( options );
				}
			};
		}

		showMessage() {
			wp.data.dispatch( 'core/notices' ).createErrorNotice( i18n.message, {
				id: 'meta-box-validation',
				isDismissible: true
			} );
		}
	};

	// Run on document ready.
	function init() {
		if ( rwmb.isGutenberg ) {
			var advanced = new GutenbergValidation( '.metabox-location-advanced' ),
				normal = new GutenbergValidation( '.metabox-location-normal' ),
				side = new GutenbergValidation( '.metabox-location-side' );

			side.init();
			normal.init();
			advanced.init();
			return;
		}

		// Edit post, edit term, edit user, front-end form.
		var $forms = $( '#post, #edittag, #your-profile, .rwmb-form' );
		$forms.each( function () {
			var form = new Validation( this );
			form.init();
		} );
	};

	rwmb.$document
		.on( 'mb_ready', init );

} )( jQuery, rwmb, rwmbValidation );
