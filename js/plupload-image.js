jQuery( document ).ready( function( $ )
{
	// Object containing all the plupload uploaders
	var rwmb_image_uploaders = {},
		max;

	// Hide "Uploaded files" title as long as there are no files uploaded
	// Note that we can have multiple upload forms in the page, so relative path to current element is important
	$( '.rwmb-uploaded' ).each( function()
	{
		var $this = $( this ),
			$lis = $this.children(),
			$title = $this.siblings( '.rwmb-uploaded-title' );
		if ( 0 == $lis.length )
		{
			$title.addClass( 'hidden' );
			$this.addClass( 'hidden' );
		}
	} );

	// Hide "Uploaded files" title if there are no files uploaded after deleting files
	$( '.rwmb-images' ).on( 'click', '.rwmb-delete-file', function()
	{
		// Check if we need to show drop target
		var $images = $( this ).parents( '.rwmb-images' ),
			uploaded = $images.children().length - 1, // -1 for the one we just deleted
			$dragndrop = $images.siblings( '.rwmb-drag-drop' );

		if ( 0 == uploaded )
		{
			$images.siblings( '.rwmb-uploaded-title' ).addClass( 'hidden' );
			$images.addClass( 'hidden' );
		}

		// After delete files, show the Drag & Drop section
		$dragndrop.show();
	} );

	// Using all the image prefixes
	$( 'input:hidden.rwmb-image-prefix' ).each( function()
	{
		var prefix = $( this ).val(),
			nonce = $( '#nonce-upload-images_' + prefix ).val();

		// Adding container, browser button and drag ang drop area
		rwmb_plupload_init = $.extend( {
			container    : prefix + '-container',
			browse_button: prefix + '-browse-button',
			drop_element : prefix + '-dragdrop'
		}, rwmb_plupload_defaults );

		// Add field_id to the ajax call
		rwmb_plupload_init['multipart_params'] = {
			action  : 'rwmb_plupload_image_upload',
			field_id: prefix,
			_wpnonce: nonce,
			post_id : $( '#post_ID' ).val(),
			force_delete: $( this ).data( 'force_delete' )
		};

		// Create new uploader
		rwmb_image_uploaders[prefix] = new plupload.Uploader( rwmb_plupload_init );
		rwmb_image_uploaders[prefix].init();

		rwmb_image_uploaders[prefix].bind( 'FilesAdded', function( up, files )
		{
			var max_file_uploads = $( '#' + this.settings.container + ' .max_file_uploads' ).val(),
				uploaded = $( '#' + this.settings.container + ' .rwmb-uploaded' ).children().length,
				msg = 'You may only upload ' + max_file_uploads + ' file';

			if ( max_file_uploads > 1 )
				msg += 's';

			// Remove files from queue if exceed max file uploads
			if ( ( uploaded + files.length ) > max_file_uploads )
			{
				for ( var i = files.length; i--; )
				{
					up.removeFile( files[i] );
				}
				alert( msg );
				return false;
			}

			// Hide drag & drop section if reach max file uploads
			if ( ( uploaded + files.length ) == max_file_uploads )
				$( '#' + this.settings.container ).find( '.rwmb-drag-drop' ).hide();

			max = parseInt( up.settings.max_file_size, 10 );

			// Upload files
			plupload.each( files, function( file )
			{
				add_loading( up, file );
				add_throbber( file );
				if ( file.size >= max )
					remove_error( file );
			} );
			up.refresh();
			up.start();
		} );

		rwmb_image_uploaders[prefix].bind( 'Error', function( up, e )
		{
			add_loading( up, e.file );
			remove_error( e.file );
			up.removeFile( e.file );
		} );

		rwmb_image_uploaders[prefix].bind( 'UploadProgress', function( up, file )
		{
			var $uploaded = $( '#' + this.settings.container + ' .rwmb-uploaded' ),
				$uploaded_title = $( '#' + this.settings.container + ' .rwmb-uploaded-title' );

			// Update the loading div
			$( 'div.rwmb-image-uploading-bar', 'li#' + file.id ).css( 'height', file.percent + '%' );

			// Show them all
			$uploaded.removeClass( 'hidden' );
			$uploaded_title.removeClass( 'hidden' );
		} );

		rwmb_image_uploaders[prefix].bind( 'FileUploaded', function( up, file, response )
		{
			var res = wpAjax.parseAjaxResponse( $.parseXML( response.response ), 'ajax-response' );
			false === res.errors ? $( 'li#' + file.id ).replaceWith( res.responses[0].data ) : remove_error( file );
		} );
	} );

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
	function add_loading( up, file )
	{
		$list = $( '#' + up.settings.container ).find( 'ul' );
		$list.append( "<li id='" + file.id + "'><div class='rwmb-image-uploading-bar'></div><div id='" + file.id + "-throbber' class='rwmb-image-uploading-status'></div></li>" );
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
