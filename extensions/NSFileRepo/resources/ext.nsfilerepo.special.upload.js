( function ( mw, $, d, undefined ) {
	function _setDestName() {
		var prefix = $( '#mw-input-wpNSFR_Namespace' ).val(),
			destName = $( '#wpDestFile' ).val(),
			destFileParts = [ destName ];
		if ( prefix !== '-' ) {
			destFileParts.unshift( prefix );
		}

		$( '#mw-input-wpNSFR_DestFile' ).val( destFileParts.join( ':' ) );
	}

	$( d ).on( 'change', '#mw-input-wpNSFR_Namespace', _setDestName );
	$( d ).on( 'change', '#wpUploadFile', _setDestName );
	$( d ).on( 'change', '#wpDestFile', _setDestName );

	$( d ).on( 'submit', '#mw-upload-form', function () {
		$( '#wpDestFile' ).val( $( '#mw-input-wpNSFR_DestFile' ).val() );
	} );

}( mediaWiki, jQuery, document ) );
