( function ( $, rwmb, i18n ) {
	'use strict';

	class Validation {
		constructor( formSelector ) {
			this.$form = $( formSelector );
			this.validationElements = this.$form.find( '.rwmb-validation' );
			this.showAsterisks();
			this.getSettings();
		}

		init() {
			this.$form.on( 'submit', function() {
				// Update underlying textarea before submit.
				if ( typeof tinyMCE !== 'undefined' ) {
					tinyMCE.triggerSave();
				}
			} ).validate( this.settings );
		}

		showAsterisks() {
			this.validationElements.each( function () {
				var data = $( this ).data( 'validation' );

				$.each( data.rules, function ( k, v ) {
					if ( ! v['required'] ) {
						return;
					}
					var $el = $( '[name="' + k + '"]' );
					if ( ! $el.length ) {
						return;
					}
					$el.closest( '.rwmb-input' ).siblings( '.rwmb-label' ).find( 'label' ).append( '<span class="rwmb-required">*</span>' );
				} );
			} );
		}

		getSettings() {
			this.settings = {
				ignore: ':not([class|="rwmb"]:visible)',
				errorPlacement: function( error, element ) {
					error.appendTo( element.closest( '.rwmb-input' ) );
				},
				errorClass: 'rwmb-error',
				errorElement: 'p',
				invalidHandler: this.error.bind( this )
			};

			// Gather all validation rules.
			var that = this;
			this.validationElements.each( function () {
				$.extend( true, that.settings, $( this ).data( 'validation' ) );
			} );
		}

		error() {
			var that = this;

			// Re-enable the submit ( publish/update ) button and hide the ajax indicator
			$( '#publish' ).removeClass( 'button-primary-disabled' );
			$( '#ajax-loading' ).attr( 'style', '' );
			$( '#rwmb-validation-message' ).remove();
			that.$form.before( '<div id="rwmb-validation-message" class="notice notice-error is-dismissible"><p>' + i18n.message + '</p></div>' );

			// Custom event for showing error fields inside tabs/hidden divs. Use setTimeout() to run after error class is added to inputs.
			setTimeout( function() {
				that.$form.trigger( 'after_validate' );
			}, 200 );
		}
	};

	class GutenbergValidation extends Validation {
		init() {
			var that = this,
				editor = wp.data.dispatch( 'core/editor' ),
				savePost = editor.savePost; // Reference original method.

			editor.savePost = function() {
				that.$form.validate( that.settings );

				if ( that.$form.valid() ) {
					// Call the original method.
					savePost();
				}
			};
		}

		error() {
			var that = this;

			wp.data.dispatch( 'core/notices' ).createErrorNotice( i18n.message, {
				id: 'meta-box-validation',
				isDismissible: true
			} );

			setTimeout( function() {
				that.$form.trigger( 'after_validate' );
			}, 200 );
		}
	};

	// Run on document ready.
	$( function() {
		// Edit post form, edit term form, edit user form, front-end form.
		var validation = rwmb.isGutenberg ? new GutenbergValidation( '.metabox-location-normal' ) : new Validation( '#post, #edittag, #your-profile, .rwmb-form' );
		validation.init();
	} );
} )( jQuery, rwmb, rwmbValidate );
