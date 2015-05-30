jQuery( function ( $ )
{
	'use strict';

	/**
	 * Select all/none for select tag
	 */
	function selectToggle( inputWrapper )
	{
		var $link = $( '.rwmb-select-all a', inputWrapper ),
			$element = $( '.rwmb-select-advanced', inputWrapper );

		$link.click( function()
		{
			var $type = $( this ).hasClass( 'select-all' ) ? 'all' : 'none';

			if ( 'all' == $type )
			{
				var selected = [];
				$element.find( 'option' ).each( function( i, e )
				{
					var $value = $( e ).attr( 'value' );

					if ( $value != '' )
					{
						selected[selected.length] = $value;
					}
				} );
				$element.select2( 'val', selected );
			}
			else
			{
				$element.select2( "val", "" );
			}
		} );
	}

	/**
	 *
	 */
	function bindSelectAllEvent( selector )
	{
		var $input = selector.closest( '.rwmb-input' ),
			$clone = $( '.rwmb-clone', $input );

		if ( $clone.length )
		{
			$clone.each( function ()
			{
				selectToggle( $( this ) );
			} );
		}
		else
		{
			selectToggle( $input );
		}
	}

	/**
	 * Turn select field into beautiful dropdown with select2 library
	 * This function is called when document ready and when clone button is clicked (to update the new cloned field)
	 *
	 * @return void
	 */
	function update()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
		$this.siblings( '.select2-container' ).remove();
		$this.show().select2( options );

		bindSelectAllEvent( $this );
	}

	$( ':input.rwmb-select-advanced' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-select-advanced', update );
} );
