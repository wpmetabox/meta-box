function autoCompleteInit(auto_id, field_name, services) {
	$ = jQuery;
	function split( val ) {
	    return val.split( /,\s*/ );
	}
	function extractLast( term ) {
	    return split( term ).pop();
	}

	$( "#" + auto_id )
	  // don't navigate away from the field on tab when selecting an item
	.bind( "keydown", function( event ) {
	    if ( event.keyCode === $.ui.keyCode.TAB && $( this ).autocomplete( "instance" ).menu.active ) {
	        event.preventDefault();
	    }
	})
    .autocomplete({
		minLength: 0,
		source: function( request, response ) {
		    // delegate back to autocomplete, but extract the last term
		    response( $.ui.autocomplete.filter(
		        services, extractLast( request.term )
		    ) );
		},
		focus: function() {
		    // prevent value inserted on focus
		    return false;
		},
		select: function( event, ui ) {
			var id = ui.item.value.match(/#(\d+)\s/)[1];
			var name = ui.item.value.match(/#\d+\s-\s(.+)/)[1];

			$(".autocompleteResults").append('<div class="lineAutocomplete">' +
				'<div class="id">' +
						'<p>#' + id + '</p>' +
			    '</div>' +
			    '<div class="name">' +
						'<p>' + name + ' +</p>' +
			    '</div>' +
			    '<div class="actions">' +
			        '<p>' + translated_strings.delete + '</p>' +
			    '</div>' +
			    '<div class="clear"></div>' +
			    '<input type="hidden" class="rwmb-autocomplete" name="' + field_name + '" value="' + id + '">' +
			'</div>');

			// reinitialize value
			this.value = '';

			return false;
        }
    });

    // handle remove action
	$(document).on("click", ".lineAutocomplete .actions", function() {

		// remove line
		$(this).parent(".lineAutocomplete").remove();

	});
}