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
			this.$form.validate( this.settings );
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
				invalidHandler: this.invalidHandler.bind( this ),
				submitHandler: this.submitHandler.bind( this )
			};

			// Gather all validation rules.
			var that = this;
			this.validationElements.each( function () {
				$.extend( true, that.settings, $( this ).data( 'validation' ) );
			} );
		}

		invalidHandler() {
			this.showMessage();

			// Custom event for showing error fields inside tabs/hidden divs. Use setTimeout() to run after error class is added to inputs.
			var that = this;
			setTimeout( function() {
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

		submitHandler( form ) {
			// Update underlying textarea before submit.
			if ( typeof tinyMCE !== 'undefined' ) {
				tinyMCE.triggerSave();
			}

			form.submit();
		}
	};

	class GutenbergValidation extends Validation {
		init() {
			var that = this,
				editor = wp.data.dispatch( 'core/editor' ),
				savePost = editor.savePost; // Reference original method.

			// Change the editor method.
			editor.savePost = function() {
				that.$form.validate( that.settings );

				// Must call savePost() here instead of in submitHandler() because the form has inline onsubmit callback.
				if ( that.$form.valid() ) {
					savePost();
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
	$( function() {
		if ( rwmb.isGutenberg ) {
			var normal = new GutenbergValidation( '.metabox-location-normal' ),
				side = new GutenbergValidation( '.metabox-location-side' );

			normal.init();
			side.init();
		} else {
			// Edit post, edit term, edit user, front-end form.
			var form = new Validation( '#post, #edittag, #your-profile, .rwmb-form' );
			form.init();
		}
	} );
} )( jQuery, rwmb, rwmbValidate );
