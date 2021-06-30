( function ( $ ) {
	'use strict';
	$( 'body' ).on(
		'click',
		'.attachment',
		function(){
			tinymce.init(
				{
					selector: "textarea.rwmb-wysiwyg",
					mode : "exact",
					elements : 'pre-details',
					theme: "modern",
					skin: "lightgray",
					menubar : false,
					statusbar : false,
					toolbar: [
					"bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | undo redo"
					],
					plugins : "paste",
					paste_auto_cleanup_on_paste : true,
					paste_postprocess : function( pl, o ) {
						o.node.innerHTML = o.node.innerHTML.replace( /&nbsp;+/ig, " " );
					}
				}
			)
		}
	);
	$( ".wp-editor-tabs button.switch-html" ).click();
	$( ".wp-editor-tabs button.switch-tmce" ).click();
} )( jQuery );
