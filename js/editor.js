jQuery( function ( $ ) {

	function update() {
		var $this = $( this ),
			options = $this.data(),
			id = $this.get(0).id;

		$this.show().closest( '.wp-editor-wrap' ).replaceWith($this);
		wp.editor.initialize( id, options );
	}
	$( ':input.rwmb-editor' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-editor', update );

} );
