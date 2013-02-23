jQuery( document ).ready( function( $ )
{
	// Hide "Uploaded files" title if there are no files uploaded after deleting files
	$( '.rwmb-images' ).on( 'click', '.rwmb-delete-file', function()
	{
		// Check if we need to show drop target
		var $dragndrop = $( this ).parents( '.rwmb-images' ).siblings( '.rwmb-drag-drop' );

		// After delete files, show the Drag & Drop section
		$dragndrop.removeClass('hidden');
	} );
	
	$('.rwmb-drag-drop').each(function() 
	{
		//Declare vars
		var $dropArea = $( this ),
			$imageList = $dropArea.siblings( '.rwmb-uploaded' ),
			uploaderData = $dropArea.data( 'js_options' ),
			rwmbUploader = {};
			
		//Extend uploaderData
		uploaderData.multipart_params = $.extend(
			{
				_ajax_nonce	:  $dropArea.data( 'upload_nonce' ),
				post_id 	: $( '#post_ID' ).val()
			},	
			uploaderData.multipart_params
		);
		
		//Create uploader
		rwmbUploader = new plupload.Uploader( uploaderData );
		rwmbUploader.init();
		
		//Add files
		rwmbUploader.bind( 'FilesAdded', function( up, files ) 
		{
			var max_file_uploads = $imageList.data('max_file_uploads'),
				uploaded = $imageList.children().length,
				msg = 'You may only upload ' + max_file_uploads + ' file';

			if ( max_file_uploads > 1 )
				msg += 's';
				
			// Remove files from queue if exceed max file uploads
			if ( max_file_uploads > 0  && ( uploaded + files.length ) > max_file_uploads )
			{
				if( uploaded < max_file_uploads ){
					var diff = max_file_uploads - uploaded;
					up.splice( diff - 1, files.length - diff );
					files = up.files;
				}
				alert( msg );				
			}
			
			// Hide drag & drop section if reach max file uploads
			if ( ( uploaded + files.length ) >= max_file_uploads ) $dropArea.addClass( 'hidden' );

			max = parseInt( up.settings.max_file_size, 10 );

			// Upload files
			plupload.each( files, function( file )
			{
				add_loading( up, file, $imageList );
				add_throbber( file );
				if ( file.size >= max )
					remove_error( file );
			} );
			up.refresh();
			up.start();
			
		} );
		
		rwmbUploader.bind( 'Error', function( up, e )
		{
			add_loading( up, e.file, $imageList );
			remove_error( e.file );
			up.removeFile( e.file );
		} );
		
		rwmbUploader.bind( 'FileUploaded', function( up, file, response )
		{
			var res = wpAjax.parseAjaxResponse( $.parseXML( response.response ), 'ajax-response' );
			false === res.errors ? $( 'li#' + file.id ).replaceWith( res.responses[0].data ) : remove_error( file );
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
	function remove_error( file )
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
	function add_loading( up, file, $ul )
	{
		$ul.append( "<li id='" + file.id + "'><div class='rwmb-image-uploading-bar'></div><div id='" + file.id + "-throbber' class='rwmb-image-uploading-status'></div></li>" );
	}

	/**
	 * Adds loading throbber while waiting for a response
	 *
	 * @return void
	 */
	function add_throbber( file )
	{
		$( '#' + file.id + '-throbber' ).html( "<img class='rwmb-loader' height='64' width='64' src='" + RWMB.url + "img/loader.gif'/>" );
	}
} );
