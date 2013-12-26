jQuery( document ).ready( function( $ )
{
	// Add more file
	$( '.rwmb-radio' ).on("click", function(){
			var $this = $( this ),
			$parent = $this.parents( 'label' );
			$parent.siblings( 'label' ).removeClass( 'rwmb-radio-checked' );
			$parent.addClass( 'rwmb-radio-checked' );
	});
});
