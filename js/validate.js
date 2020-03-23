jQuery( function ( $ ) {
	'use strict';

	var rules = {
		invalidHandler: function () {
			// Re-enable the submit ( publish/update ) button and hide the ajax indicator
			$( '#publish' ).removeClass( 'button-primary-disabled' );
			$( '#ajax-loading' ).attr( 'style', '' );
			$form.siblings( '#message' ).remove();
			$form.before( '<div id="message" class="notice notice-error is-dismissible"><p>' + rwmbValidate.summaryMessage + '</p></div>' );

			// Custom event for showing error fields inside tabs/hidden divs. Use setTimeout() to run after error class is added to inputs.
			setTimeout( function() {
				$form.trigger( 'after_validate' );
			}, 200 );
		},
		ignore: ':not([class|="rwmb"]:visible)',
		errorPlacement: function( error, element ) {
			error.appendTo( element.closest( '.rwmb-input' ) );
		},
		errorClass: 'rwmb-error',
		errorElement: 'p'
	};

	// Edit post form.
	var $form = $( '#post, .rwmb-form' );

	// Edit user form.
	if ( ! $form.length ) {
		$form = $( '#your-profile' );
	}

	// Edit term form.
	if ( ! $form.length ) {
		$form = $( '#edittag' );
	}

	// Gather all validation rules.
	$( '.rwmb-validation-rules' ).each( function () {
		var subRules = $( this ).data( 'rules' );
		$.extend( true, rules, subRules );

		// Required field styling.
		$.each( subRules.rules, function ( k, v ) {
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

	// Execute.
	$form.on( 'submit', function() {
		// Update underlying textarea before submit validation.
		if ( typeof tinyMCE !== 'undefined' ) {
			tinyMCE.triggerSave();
		}
	} ).validate( rules );
} );
