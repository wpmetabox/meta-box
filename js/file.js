jQuery( document ).ready( function( $ )
{
	// Add more file
	$( '.rwmb-add-file' ).click( function()
	{
		var $this = $( this ),
			$fields = $this.siblings( '.file-input' ),
			$first = $fields.first(),
			$fileList = $this.closest( '.rwmb-input' ).find( '.rwmb-uploaded' ),
			fileCount = $fileList.children( 'li' ).length,
			maxFileUploads = $fileList.data( 'max_file_uploads' );

		if ( ($fields.length + fileCount) < maxFileUploads || maxFileUploads <= 0 )
		{
			$first.clone().insertBefore( $this );
		}

		return false;
	} );

	// Delete file via Ajax
	$( '.rwmb-uploaded' ).on( 'click', '.rwmb-delete-file', function()
	{
		var $this = $( this ),
			$parent = $this.parents( 'li' ),
			$container = $this.closest( '.rwmb-uploaded' ),
			data = {
				action: 'rwmb_delete_file',
				_ajax_nonce: $container.data( 'delete_nonce' ),
				post_id: $( '#post_ID' ).val(),
				field_id: $container.data( 'field_id' ),
				attachment_id: $this.data( 'attachment_id' ),
				force_delete: $container.data( 'force_delete' )
			};

		$.post( ajaxurl, data, function( r )
		{
			var res = wpAjax.parseAjaxResponse( r, 'ajax-response' );

			if ( res.errors )
			{
				alert( res.responses[0].errors[0].message );
			}
			else
			{
				$parent.remove();
				$container.trigger( 'update.rwmbFile' );
			}
		}, 'xml' );

		return false;
	} );

	$( 'body' ).on( 'update.rwmbFile', '.rwmb-uploaded', function()
	{
		var $fileList = $( this ),
			maxFileUploads = $fileList.data( 'max_file_uploads' ),
			$uploader = $fileList.siblings( '.new-files' ),
			numFiles = $fileList.children().length;

		numFiles > 0 ? $fileList.removeClass( 'hidden' ) : $fileList.addClass( 'hidden' );

		// Return if maxFileUpload = 0
		if ( maxFileUploads === 0 )
			return false;

		// Hide files button if reach max file uploads
		numFiles >= maxFileUploads ? $uploader.addClass( 'hidden' ) : $uploader.removeClass( 'hidden' );

		return false;
	} );
} );
