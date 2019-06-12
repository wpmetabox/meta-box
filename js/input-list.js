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
		.on( 'change', '.rwmb-input-list.rwmb-collapse input[type="checkbox"]', update )
		.on( 'clone', '.rwmb-input-list.rwmb-collapse input[type="checkbox"]', update );
	$( '.rwmb-input-list.rwmb-collapse input[type="checkbox"]' ).each( update );

	$( document ).on( 'click', '.rwmb-input-list-select-all-none', function( e ) {
		e.preventDefault();

		var $this = $( this ),
			checked = $this.data( 'checked' );

		if ( undefined === checked ) {
			checked = true;
		}

		$this.parent().siblings( '.rwmb-input-list' ).find( 'input' ).prop( 'checked', checked );

		checked = ! checked;
		$this.data( 'checked', checked );
	} );
} );
