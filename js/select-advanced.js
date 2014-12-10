jQuery( function ( $ )
{
	'use strict';

	function rwmb_update_select_advanced()
	{
		var $this = $( this ),
			options = $this.data( 'options' ),
			width = $this.siblings( '.select2-container' ).width();
		$this.siblings( '.select2-container' ).remove();
		$this.removeAttr('style');
		$this.select2( options );
		$this.siblings( '.select2-container' ).width(width);
	}

	$( ':input.rwmb-select-advanced' ).each( rwmb_update_select_advanced );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-select-advanced', rwmb_update_select_advanced );
} );
