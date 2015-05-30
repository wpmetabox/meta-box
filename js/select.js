jQuery( function ( $ )
{
	'use strict';

	/**
	 * Select all/none for select tag
	 */
	function selectToggle( inputWrapper )
	{
		var $link = $( '.rwmb-select-all a', inputWrapper ),
			$element = $( '.rwmb-select', inputWrapper );

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
				$element.val( selected );
			}
			else
			{
				$element.val('');
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
		bindSelectAllEvent( $(this) );
	}

	$( ':input.rwmb-select' ).each( update );
	$( '.rwmb-input' ).on( 'clone', ':input.rwmb-select', update );
} );
