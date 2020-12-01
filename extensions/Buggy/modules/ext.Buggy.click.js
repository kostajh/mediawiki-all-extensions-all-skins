( function ( $ ) {
	$( '#firstHeading' ).on( 'click', function () { callANonExistentFunctionOnClick(); } ).trigger( 'click' );
}( jQuery ) );
