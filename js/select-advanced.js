jQuery( function ( $ )
{
	'use strict';

	function rwmb_update_select_advanced()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
		$this.siblings( '.select2-container' ).remove();
		$this.select2( options );
	}

	$( ':input.rwmb-select-advanced' ).each( rwmb_update_select_advanced );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-select-advanced', rwmb_update_select_advanced );
} );
