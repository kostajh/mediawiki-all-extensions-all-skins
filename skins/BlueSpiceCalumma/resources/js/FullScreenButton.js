( function( d, $, mw ) {
	$( d ).on( 'click', '.calumma-full-screen-button', function( e ){
		e.preventDefault();

		var target =  $( this ).attr( 'data-toggle' );

		if ( $( this ).hasClass( target ) ){
			$( this ).removeClass( target );
			$( 'body.mediawiki' ).removeClass( target );
			bs.calumma.cookie.set( 'Calumma_'+target, 'false' );
		} else {
			if ( !( $( 'body' ).hasClass( 'navigation-main-collapse' ) ) ){
				$( '.navigation-main .sidebar-toggle' ).trigger( 'click' );
			}
			if ( !( $( 'body' ).hasClass( 'sitetools-main-collapse' ) ) ){
				$( '.sitetools-main .sidebar-toggle' ).trigger( 'click' );
			}
			$( this ).addClass( target );
			$( 'body.mediawiki' ).addClass( target );
			bs.calumma.cookie.set( 'Calumma_'+target, 'true' );
		}
	});
})( document, jQuery, mediaWiki );

