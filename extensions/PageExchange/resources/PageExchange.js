/* JavaScript for the Page Exchange extension */

$( '.pageExchangeAdditionalPages' ).hide();

$( '.pageExchangeToggle' ).on( 'click', function () {
	var $toggleLink = $( this );
	$toggleLink.siblings( '.pageExchangeAdditionalPages' ).each( function () {
		if ( $( this ).is( ':hidden' ) ) {
			$( this ).show();
			$toggleLink.text( 'show less' );
		} else {
			$( this ).hide();
			$toggleLink.text( 'show more' );
		}
	} );
} );
