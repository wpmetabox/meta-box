jQuery( document ).ready( function($)
{
	//Load loading and error images
	for(x in rwmb_plupload_status_icons)
	{
		$('<img />')
			.load(
				function(){
					$('body').append( $(this) );
				}
			)
			.hide()
			.attr('src', rwmb_plupload_status_icons[x]);	
	};
	
	// Object containing all the plupload uploaders
	var 
		rwmb_image_uploaders	= {},
		hundredMB				= null,
		max						= null
	;
	// Hide "Uploaded files" title as long as there are no files uploaded
	if ( 1 == $( '.rwmb-uploaded' ).children().length )
		$( '.rwmb-uploaded-title' ).addClass( 'hidden' );
	// Check on mouseenter & -leave if we got files and add the "Uploaded files" title
	$( '.rwmb-drag-drop' ).bind( 
		'mouseenter mouseleave', 
		function() 
		{
			if ( 1 < $( '.rwmb-uploaded' ).children().length )
				$( '.rwmb-uploaded-title' ).removeClass( 'hidden' );
		}
	);
	$( '.rwmb-delete-file' ).bind( 
		'click',
		function()
		{
			if ( 1 >= $( '.rwmb-uploaded' ).children().length )
				$( '.rwmb-uploaded-title' ).addClass( 'hidden' );
		}
	);

	// Using all the image prefixes
	$( 'input:hidden.rwmb-image-prefix' ).each( function() 
	{
		prefix = $( this ).val();	
		// Adding container, browser button and drag ang drop area
		rwmb_plupload_init = $.extend( 
			{
				container:		prefix + '-container',
				browse_button:	prefix + '-browse-button',
				drop_element:	prefix + '-dragdrop'				
			},
			rwmb_plupload_defaults 
		);
		// Add field_id to the ajax call
		$.extend( 
			rwmb_plupload_init.multipart_params, 
			{
				field_id:	prefix
			}
		);
		// Create new uploader
		rwmb_image_uploaders[ prefix ] = new plupload.Uploader( rwmb_plupload_init );
		rwmb_image_uploaders[ prefix ].init();
		//
		rwmb_image_uploaders[ prefix ].bind( 
			'FilesAdded', 
			function( up, files )
			{
				hundredMB	= 100 * 1024 * 1024, 
				max			= parseInt( up.settings.max_file_size, 10 );
				plupload.each(
					files, 
					function( file )
					{
						template =  $( '#' + up.settings.container ).find( 'li.rwmb-image-template' );
						template
							.clone( true )
							.removeClass( 'hidden' )
							.removeClass( 'rwmb-image-template' )
							.find('img.rwmb-image')
							.attr('id',file.id)
							.attr('src', rwmb_plupload_status_icons['loading'])
							.end()
							.insertBefore(template);	
						if ( file.size >= max )
						{
							$( 'img#' + file.id )
								.attr('src', rwmb_plupload_status_icons['error'])
								.closest('li')
								.delay(1600)
								.fadeOut(
									'slow', 
									function() 
									{
										$(this).remove();
									}
								);
						}
					} 
				);
				up.refresh();
				up.start();
			}
		);

		rwmb_image_uploaders[ prefix ].bind(
			'Error', 
			function( up, e ) 
			{
				template =  $( "#" + up.settings.container ).find( 'li.rwmb-image-template' );
				template
					.clone(true)
					.removeClass('hidden')
					.removeClass('rwmb-image-template')
					.find('img.rwmb-image')
					.attr('src', rwmb_plupload_status_icons['error'])
					.end()
					.insertBefore(template)
					.delay(1600)
					.fadeOut(
						'slow', 
						function() 
						{
							$(this).remove();
						}
					);	
				up.removeFile(e.file);
			}
		);

		rwmb_image_uploaders[ prefix ].bind(
			'FileUploaded', 
			function( up, file, response ) 
			{
				response_xml = $.parseXML( response.response );
				res = wpAjax.parseAjaxResponse( response_xml, 'ajax-response' );
				if ( false === res.errors )
				{
					res		= res.responses[0];
					img_id	= res.data;
					img_src	= res.supplemental.thumbnail;
					img_edit = res.supplemental.edit_link;
					$('img#'+file.id)
						.attr('src',img_src)
						.siblings('div.rwmb-image-bar')
						.removeClass('hidden')
						.find('a.rwmb-edit-file')
						.attr('href',img_edit)
						.siblings('a.rwmb-delete-file')
						.attr('rel',img_id)
						.closest('li')
						.attr('id','item_' + img_id);
				}
				else
				{
					$('img#'+file.id)
						.attr('src', rwmb_plupload_status_icons[loading])
						.closest('li')
						.delay(1600)
						.fadeOut(
							'slow', 
							function() 
							{
								$(this).remove();
							}
						);
				}
			});
	});
});