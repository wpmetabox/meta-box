jQuery( document ).ready( function ( $ )
{
	var $form = $( '#post' );

	// Required field styling
	$.each( rwmb.validationOptions.rules, function( k, v )
	{
		if ( v['required'] )
			$( '#' + k ).parent().siblings( '.rwmb-label' ).addClass( 'required' ).append( '<span>*</span>' );
	} );

	rwmb.validationOptions.invalidHandler = function( form, validator )
	{
		// Re-enable the submit ( publish/update ) button and hide the ajax indicator
		$( '#publish' ).removeClass( 'button-primary-disabled' );
		$( '#ajax-loading' ).attr( 'style','' );
		$form.siblings( '#message' ).remove();
		$form.before( '<div id="message" class="error"><p>' + rwmb.summaryMessage  + '</p></div>' );
	};

	$form.validate( rwmb.validationOptions );
} );