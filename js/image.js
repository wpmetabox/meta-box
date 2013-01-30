jQuery( document ).ready( function( $ )
{
	// Reorder images
	$( '.rwmb-images' ).each( function()
	{
		var $this    = $( this ),
			$container = $this.closest('.rwmb-uploaded'),
			data     = {
				action  : 'rwmb_reorder_images',
				_wpnonce: $container.data('reorder_nonce'),
				post_id : $( '#post_ID' ).val(),
				field_id: $container.data('field_id')
			};
		$this.sortable( {
			placeholder: 'ui-state-highlight',
			items      : 'li',
			update     : function()
			{
				data.order = $this.sortable( 'serialize' );

				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
				}, 'xml' );
			}
		} );
	} );
} );