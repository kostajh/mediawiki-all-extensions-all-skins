( function( d, $, mw ) {

	$( '.calumma-toggle-button' ).on( 'click', function( e ){

		e.preventDefault();

		var target =  $( this ).attr( 'data-toggle' );

		if( $( 'body' ).hasClass( target ) ){
			$( 'body' ).removeClass( target );
			bs.calumma.cookie.set( 'Calumma_'+target, 'false' );
		}
		else{
			$( 'body' ).addClass( target );
			bs.calumma.cookie.set( 'Calumma_'+target, 'true' );
		}
	});
})( document, jQuery, mediaWiki );

