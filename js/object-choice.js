jQuery( function( $ )
{
	'use strict';
	function updateChecklist()
	{
		var $this = $( this ),
			$children = $this.closest( 'li' ).children('ul');

		if ( $this.is( ':checked' ) )
		{
			$children.removeClass( 'hidden' );
		}
		else
		{
			$children
				.addClass( 'hidden' )
				.find( 'input' )
				.removeAttr( 'checked' );
		}
	}

	$( '.rwmb-input' )
		.on( 'change', '.rwmb-choice-list.collapse :checkbox', updateChecklist )
		.on( 'clone', '.rwmb-choice-list.collapse :checkbox', updateChecklist );
	$( '.rwmb-choice-list.collapse :checkbox' ).each( updateChecklist );


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
