jQuery( document ).ready( function ( $ )
{
	'use strict';

	// Add more file
	$( '.rwmb-add-file' ).each( function ()
	{
		var $this = $( this ),
			$uploads = $this.siblings( '.file-input' ),
			$first = $uploads.first(),
			uploadCount = $uploads.length,
			$fileList = $this.closest( '.rwmb-input' ).find( '.rwmb-uploaded' ),
			fileCount = $fileList.children( 'li' ).length,
			maxFileUploads = $fileList.data( 'max_file_uploads' );

		// Hide "Add New File" and input fields when loaded
		if ( maxFileUploads > 0 )
		{
			if ( uploadCount + fileCount >= maxFileUploads )
			{
				$this.hide();
			}
			if ( fileCount >= maxFileUploads )
			{
				$uploads.hide();
			}
		}

		$this.click( function ()
		{
			// Clone upload input only when needed
			if ( maxFileUploads <= 0 || uploadCount + fileCount < maxFileUploads )
			{
				$first.clone().insertBefore( $this );
				uploadCount++;

				// If there're too many upload inputs, hide "Add New File"
				if ( maxFileUploads > 0 && uploadCount + fileCount >= maxFileUploads )
				{
					$this.hide();
				}
			}

			return false;
		} );
	} );

	// Delete file via Ajax
	$( '.rwmb-uploaded' ).on( 'click', '.rwmb-delete-file', function ()
	{
		var $this = $( this ),
			$parent = $this.parents( 'li' ),
			$container = $this.closest( '.rwmb-uploaded' ),
			data = {
				action       : 'rwmb_delete_file',
				_ajax_nonce  : $container.data( 'delete_nonce' ),
				post_id      : $( '#post_ID' ).val(),
				field_id     : $container.data( 'field_id' ),
				attachment_id: $this.data( 'attachment_id' ),
				force_delete : $container.data( 'force_delete' )
			};

		$.post( ajaxurl, data, function ( r )
		{
			if ( !r.success )
			{
				alert( r.data );
				return;
			}

			$parent.addClass( 'removed' );

			// If transition events not supported
			var div = document.createElement( 'div' );
			if (
				!( 'ontransitionend' in window ) &&
				( 'onwebkittransitionend' in window ) && !( 'onotransitionend' in div || navigator.appName === 'Opera' )
			)
			{
				$parent.remove();
				$container.trigger( 'update.rwmbFile' );
			}

			$( '.rwmb-uploaded' ).on( 'transitionend webkitTransitionEnd otransitionend', 'li.removed', function ()
			{
				$( this ).remove();
				$container.trigger( 'update.rwmbFile' );
			} );
		}, 'json' );

		return false;
	} );

	//Remove deleted file
	$( '.rwmb-uploaded' ).on( 'transitionend webkitTransitionEnd otransitionend', 'li.removed', function ()
	{
		$( this ).remove();
	} );

	$( 'body' ).on( 'update.rwmbFile', '.rwmb-uploaded', function ()
	{
		var $fileList = $( this ),
			maxFileUploads = $fileList.data( 'max_file_uploads' ),
			$uploader = $fileList.siblings( '.new-files' ),
			numFiles = $fileList.children().length;

		if ( numFiles > 0 )
		{
			$fileList.removeClass( 'hidden' );
		}
		else
		{
			$fileList.addClass( 'hidden' );
		}

		// Return if maxFileUpload = 0
		if ( maxFileUploads === 0 )
		{
			return false;
		}

		// Hide files button if reach max file uploads
		if ( numFiles >= maxFileUploads )
		{
			$uploader.addClass( 'hidden' );
		}
		else
		{
			$uploader.removeClass( 'hidden' );
		}

		return false;
	} );

	// Reorder images
	$( '.rwmb-file' ).each( function ()
	{
		var $this = $( this ),
			data = {
				action     : 'rwmb_reorder_files',
				_ajax_nonce: $this.data( 'reorder_nonce' ),
				post_id    : $( '#post_ID' ).val(),
				field_id   : $this.data( 'field_id' )
			};
		$this.sortable( {
			placeholder: 'ui-state-highlight',
			items      : 'li',
			update     : function ()
			{

				data.order = $this.sortable( 'serialize' );

				$.post( ajaxurl, data );
			}
		} );
	} );
} );
