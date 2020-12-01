( function( d, $ ) {
	$( '.bs-custom-menu.header' ).on( 'click', '.dropdown-submenu a.dropdown-submenu-toggle', function( e ) {
		$( this ).next( '.dropdown-menu' ).toggle();

		if ( $( this ).parent( '.dropdown-submenu' ).hasClass( 'open' ) ) {
			$( this ).parent( '.dropdown-submenu' ).removeClass( 'open' );
		}
		else {
			var $openMenu = $( this ).parents( '.dropdown-menu' ).first().children( '.dropdown-submenu.open' );

			if ( $openMenu.length > 0 ) {
				$openMenu.each( function( index, element ){
					$( element ).children( '.dropdown-menu' ).toggle();
					$( element ).removeClass( 'open' );
				});
			}

			$( this ).parent( '.dropdown-submenu' ).addClass( 'open' );
		}

		e.stopPropagation();
		e.preventDefault();
	});

	$( d ).on( 'click', function( e ) {
		if ( $( e.target ).is( '.bs-custom-menu.header' ) === false ) {
			$( '.bs-custom-menu.header .dropdown-submenu.open' ).each( function( index, element ) {
				$( element ).removeClass( 'open' );
				$( element ).children( '.dropdown-menu').toggle();
			});
		}
	});
})( document, jQuery );
