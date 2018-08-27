window.rwmb = window.rwmb || {};
window.rwmb.editor = window.rwmb.editor || {};
jQuery( function ( $ ) {
	function update() {
		var $this = $( this ),
			options = $this.data(),
			id = $this.get(0).id;

		$this.show().closest( '.wp-editor-wrap' ).replaceWith($this);
		wp.editor.initialize( id, options );
		if ( options.media ){
			$this.closest( '.wp-editor-wrap' ).find( '.wp-editor-tools' ).prepend( $( '<div>' ).attr( {
				id: 'wp-' + id + '-media-buttons',
				class: 'wp-media-buttons'
			})
				.append( $( '<button>' ).attr( {
					class: 'button insert-media add_media',
					'data-editor': id } )
					.append( $('<span>').attr( {
							class: 'wp-media-buttons-icon'
						} ), window.tinymce.translate( 'Add Media' ) )
				) );
		}
	}
	$( ':input.rwmb-editor' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-editor', update );

} );
