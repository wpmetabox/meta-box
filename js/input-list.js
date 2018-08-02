jQuery( function ( $ ) {
	function update() {
		var $this = $( this ),
			$children = $this.closest( 'li' ).children( 'ul' );

		if ( $this.is( ':checked' ) ) {
			$children.removeClass( 'hidden' );
		} else {
			$children
				.addClass( 'hidden' )
				.find( 'input' )
				.removeAttr( 'checked' );
		}
	}

	$( '.rwmb-input' )
		.on( 'change', '.rwmb-input-list.collapse :checkbox', update )
		.on( 'clone', '.rwmb-input-list.collapse :checkbox', update );
	$( '.rwmb-input-list.collapse :checkbox' ).each( update );

	$( '.rwmb-input-list-select-all-none' ).toggle(
		function () {
			$( this ).parent().siblings( '.rwmb-input-list' ).find( 'input' ).prop( 'checked', true );
		},
		function () {
			$( this ).parent().siblings( '.rwmb-input-list' ).find( 'input' ).prop( 'checked', false );
		}
	);
} );
