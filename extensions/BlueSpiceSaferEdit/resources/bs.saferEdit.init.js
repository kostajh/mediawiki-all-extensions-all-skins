if (  mw.config.get( 'bsSaferEditIsEditMode' ) ) {
	_activateEditMode();
}

// This is an exception, only time edit mode is inited on client-side
// It would be nicer to implement extendable client-side system for detecting
// edit mode - but for only this its not worth it
mw.hook( 've.activationComplete' ).add( function () {
	_activateEditMode();
} );

function _activateEditMode() {
	new bs.saferEdit.Save( {
		section: mw.config.get( 'bsSaferEditEditSection' ),
		interval: mw.config.get( 'bsgSaferEditInterval' ) * 1000
	} );
}

if ( mw.config.get( 'bsSaferEditDisplayWarning' ) ) {
	new bs.saferEdit.Warning( {
		section: mw.config.get( 'bsSaferEditEditSection' ),
		interval: mw.config.get( 'bsgSaferEditInterval' ) * 1000
	} );
}

