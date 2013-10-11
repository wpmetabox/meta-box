jQuery( function( $ )
{
	// Hide "Uploaded files" title if there are no files uploaded after deleting files
	$( '.rwmb-images' ).on( 'click', '.rwmb-delete-file', function()
	{
		// Check if we need to show drop target
		var $dragndrop = $( this ).parents( '.rwmb-images' ).siblings( '.rwmb-drag-drop' );

		// After delete files, show the Drag & Drop section
		$dragndrop.removeClass('hidden');
	} );

	$( '.rwmb-drag-drop' ).each(function()
	{
		// Declare vars
		var $dropArea = $( this ),
			$imageList = $dropArea.siblings( '.rwmb-uploaded' ),
			uploaderData = $dropArea.data( 'js_options' ),
			uploader = {};

		// Extend uploaderData
		uploaderData.multipart_params = $.extend(
			{
				_ajax_nonce	:  $dropArea.data( 'upload_nonce' ),
				post_id 	: $( '#post_ID' ).val()
			},
			uploaderData.multipart_params
		);

		// Create uploader
		uploader = new plupload.Uploader( uploaderData );
		uploader.init();

		// Add files
		uploader.bind( 'FilesAdded', function( up, files )
		{
			var maxFileUploads = $imageList.data( 'max_file_uploads' ),
				uploaded = $imageList.children().length,
				msg = maxFileUploads > 1 ? rwmbFile.maxFileUploadsPlural : rwmbFile.maxFileUploadsSingle;

			msg = msg.replace( '%d', maxFileUploads );

			// Remove files from queue if exceed max file uploads
			if ( maxFileUploads > 0  && ( uploaded + files.length ) > maxFileUploads )
			{
				if ( uploaded < maxFileUploads )
				{
					var diff = maxFileUploads - uploaded;
					up.splice( diff - 1, files.length - diff );
					files = up.files;
				}
				alert( msg );
			}

			// Hide drag & drop section if reach max file uploads
			if ( uploaded + files.length >= maxFileUploads )
				$dropArea.addClass( 'hidden' );

			max = parseInt( up.settings.max_file_size, 10 );

			// Upload files
			plupload.each( files, function( file )
			{
				addLoading( up, file, $imageList );
				addThrobber( file );
				if ( file.size >= max )
					removeError( file );
			} );
			up.refresh();
			up.start();

		} );

		uploader.bind( 'Error', function( up, e )
		{
			addLoading( up, e.file, $imageList );
			removeError( e.file );
			up.removeFile( e.file );
		} );

		uploader.bind( 'FileUploaded', function( up, file, r )
		{
			r = $.parseJSON( r.response );
			r.success ? $( 'li#' + file.id ).replaceWith( r.data ) : removeError( file );
		} );
	});

	/**
	 * Helper functions
	 */

	/**
	 * Removes li element if there is an error with the file
	 *
	 * @return void
	 */
	function removeError( file )
	{
		$( 'li#' + file.id )
			.addClass( 'rwmb-image-error' )
			.delay( 1600 )
			.fadeOut( 'slow', function()
			{
				$( this ).remove();
			}
		);
	}

	/**
	 * Adds loading li element
	 *
	 * @return void
	 */
	function addLoading( up, file, $ul )
	{
		$ul.removeClass('hidden').append( "<li id='" + file.id + "'><div class='rwmb-image-uploading-bar'></div><div id='" + file.id + "-throbber' class='rwmb-image-uploading-status'></div></li>" );
	}

	/**
	 * Adds loading throbber while waiting for a response
	 *
	 * @return void
	 */
	function addThrobber( file )
	{
		$( '#' + file.id + '-throbber' ).html( "<img class='rwmb-loader' height='64' width='64' src='" + RWMB.url + "img/loader.gif'/>" );
	}
} );
