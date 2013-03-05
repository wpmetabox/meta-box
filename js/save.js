jQuery( document ).ready( function( $ )
{
	$( '.rwmb-meta-box').each(function(){
		var $metaBox = $(this),
			saveInterval = $metaBox.data( 'autosave' ),
			metaBoxID = $metaBox.data('meta_box_id');
		if( saveInterval !== undefined ){
			setInterval(function(){
				
				var data = $(':input',$metaBox).serialize() + '&action=' + 'rwmb_save_meta_' + metaBoxID + '&post_ID=' + $( '#post_ID' ).val() ;
				$.post( ajaxurl, data, function( r )
				{
					var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );		
					if ( res.errors )
						alert( res.responses[0].errors[0].message );
				}, 'xml' );
				
			}, 
			saveInterval * 1000)
		}
	} );
} );