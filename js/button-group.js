jQuery( function ( $ ) {
	'use strict';
	function ButtonClick() {
		var $this 	= $( this ),
		$checked 	= $( 'input', $this );

		// check status input
		if ( $checked.is(':checked') == true ) {
			$('label', $this ).addClass('selected');
		}
		// input check
		$( $checked ).click( function () {
			var $this 		= $( this ),
				$type		= $(this).attr('type'),
				$label		= $( this ).parent(),
				$checked	= $this.is(':checked');

				// check input
				if ( $checked == true && $type == 'checkbox' ) {
					$label.addClass('selected');
				}else if ( $checked == true && $type == 'radio' ) {
					$( '.rwmb-button-input-list li label' ).removeClass('selected');
					$label.addClass('selected');
				}else {
					$label.removeClass('selected');
				}

		} );	
	}
	$( '.rwmb-button-input-list li' ).each( ButtonClick );

	
} );
