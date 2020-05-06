( function ( $, rwmb, i18n ) {
	'use strict';

	var Validation = {
		// Form element.
		$form: null,

		// Validation settings.
		settings: {},

		initGutenberg: function() {
			wp.data.dispatch( 'core/editor' ).savePost = function() {
				Validation.getForm();
				Validation.getGutenbergSettings();
				let v = Validation.$form.validate( Validation.settings );
				console.log( v );
				console.log( v.valid() );
			}
		},

		initClassic: function() {
			Validation.getForm();
			Validation.getClassicSettings();
			Validation.$form.on( 'submit', function() {
				// Update underlying textarea before submit validation.
				if ( typeof tinyMCE !== 'undefined' ) {
					tinyMCE.triggerSave();
				}
			} ).validate( Validation.settings );
		},

		addAsterisks: function () {
			$( '.rwmb-validation' ).each( function () {
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
		},

		getForm: function () {
			// Classic edit post form, edit term form, edit user form, front-end form.
			Validation.$form = rwmb.isGutenberg ? $( '.metabox-location-normal' ) : $( '#post, #edittag, #your-profile, .rwmb-form' );
		},

		getClassicSettings: function () {
			Validation.getSettings();
			Validation.settings.invalidHandler = function () {
				// Re-enable the submit ( publish/update ) button and hide the ajax indicator
				$( '#publish' ).removeClass( 'button-primary-disabled' );
				$( '#ajax-loading' ).attr( 'style', '' );
				$( '#rwmb-validation-message' ).remove();
				Validation.$form.before( '<div id="rwmb-validation-message" class="notice notice-error is-dismissible"><p>' + i18n.message + '</p></div>' );

				// Custom event for showing error fields inside tabs/hidden divs. Use setTimeout() to run after error class is added to inputs.
				setTimeout( function() {
					Validation.$form.trigger( 'after_validate' );
				}, 200 );
			};
		},

		getGutenbergSettings: function() {
			Validation.getSettings();

			// Reference original method.
			Validation.savePost = wp.data.dispatch( 'core/editor' ).savePost;

			Validation.settings.invalidHandler = function() {
				wp.data.dispatch( 'core/notices' ).createErrorNotice( i18n.message, {
					id: 'meta-box-validation',
					isDismissible: true
				} );
				wp.data.dispatch( 'core/editor' ).lockPostSaving( 'meta_box' );

				setTimeout( function() {
					Validation.$form.trigger( 'after_validate' );
				}, 200 );
			};

			Validation.settings.submitHandler = function() {
				// Call original savePost method.
				Validation.savePost();
			};
		},

		getSettings: function () {
			Validation.settings = {
				ignore: ':not([class|="rwmb"]:visible)',
				errorPlacement: function( error, element ) {
					error.appendTo( element.closest( '.rwmb-input' ) );
				},
				errorClass: 'rwmb-error',
				errorElement: 'p',
			};

			// Gather all validation rules.
			$( '.rwmb-validation' ).each( function () {
				$.extend( true, Validation.settings, $( this ).data( 'validation' ) );
			} );
		},
	};

	// Run on document ready.
	$( function() {
		Validation.addAsterisks();

		if ( rwmb.isGutenberg ) {
			Validation.initGutenberg();
		} else {
			Validation.initClassic();
		}
	} );
} )( jQuery, rwmb, rwmbValidate );
