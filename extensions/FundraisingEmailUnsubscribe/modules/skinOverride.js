( function ( $ ) {
	$( function () {
		$( 'div#p-logo a' ).attr( 'href', '#' );
		 
		// Cheating a bit, this deserves its own module.
		$( 'input#unsubscribe, input#continue' ).on( 'click', function () {
			$( '#execute' ).val( 1 );
		} );
	} );
} )( jQuery );
