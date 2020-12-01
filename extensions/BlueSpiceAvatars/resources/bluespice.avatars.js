( function ( mw, $, d ) {
	var selectors = [
		"#bs-authors-imageform",
		".bs-avatars-userimage-pref.bs-userminiprofile",
		".bs-avatars-userimage-pref-btn"
	];

	$( d ).on( 'click', selectors.join( ', ' ), function( e ) {
		e.preventDefault();
		mw.loader.using( [
			"user.options",
			'ext.bluespice.extjs'
		] ).done( function() {
			Ext.onReady( function() {
				Ext.require( 'BS.Avatars.SettingsWindow', function() {
					BS.Avatars.SettingsWindow.show();
				} );
			} );
		} );
		return false;
	} );
} )( mediaWiki, jQuery, document );