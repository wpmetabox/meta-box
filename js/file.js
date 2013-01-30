jQuery( document ).ready( function( $ )
{
	// Add more file
	$( '.rwmb-add-file' ).click( function()
	{
		var $this = $( this ), $first = $this.parent().find( '.file-input:first' );

		$first.clone().insertBefore( $this );

		return false;
	} );

	// Delete file via Ajax
	$( '.rwmb-uploaded' ).on( 'click', '.rwmb-delete-file', function()
	{
		var $this = $( this ),
			$parent = $this.parents( 'li' ),
			$container = $this.closest('.rwmb-uploaded')
			data = {
				action       : 'rwmb_delete_file',
				_wpnonce     : $container.data('delete_nonce'),
				post_id      : $( '#post_ID' ).val(),
				field_id     : $container.data( 'field_id' ),
				attachment_id: $this.data( 'attachment_id' ),
				force_delete : $container.data( 'force_delete' )
			};

		$.post( ajaxurl, data, function( r )
		{
			var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );

			if ( res.errors )
				alert( res.responses[0].errors[0].message );
			else
				$parent.remove();
		}, 'xml' );

		return false;
	} );
} );