jQuery( function( $ )
{
	'use strict';

	function updateSelectTree()
	{
		var $this = $( this ),
			val = $this.val(),
			$selected = $this.siblings( "[data-parent-id='" + val + "']" ),
			$notSelected = $this.parent().find( '.rwmb-select-tree' ).not( $selected );

		$selected.removeClass( 'hidden' );
		$notSelected
			.addClass( 'hidden' )
			.find( 'select' )
			.prop( 'selectedIndex', 0 );
	}

	$( '.rwmb-input' )
		.on( 'change', '.rwmb-select-tree select', updateSelectTree )
		.on( 'clone', '.rwmb-select-tree select', updateSelectTree );
} );
