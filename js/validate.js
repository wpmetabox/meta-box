jQuery( function ( $ )
{
	'use strict';

	var $form = $( '#post' ),
		rules = {
			invalidHandler: function ()
			{
				// Re-enable the submit ( publish/update ) button and hide the ajax indicator
				$( '#publish' ).removeClass( 'button-primary-disabled' );
				$( '#ajax-loading' ).attr( 'style', '' );
				$form.siblings( '#message' ).remove();
				$form.before( '<div id="message" class="error"><p>' + rwmbValidate.summaryMessage + '</p></div>' );
			},
			ignore: ''
		};

	// Gather all validation rules
	$( '.rwmb-validation-rules' ).each( function ()
	{
		var subRules = $( this ).data( 'rules' );
		$.extend( true, rules, subRules );

		// Required field styling
		$.each( subRules.rules, function ( k, v )
		{
			if ( v['required'] )
			{
				$( '#' + k ).parent().siblings( '.rwmb-label' ).addClass( 'required' ).append( '<span>*</span>' );
			}
		} );
	} );

	// Execute
	$form.validate( rules );
} );
