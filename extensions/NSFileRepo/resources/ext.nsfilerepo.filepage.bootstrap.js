( function ( mw, $, d ) {
	$( d ).on( 'click', '#ca-move-file-namespace, .nsfr-move-file-namespace', function ( e ) {
		var me = this;
		if ( me.dialog ) {
			me.dialog.show();
		} else {
			mw.loader.using( [ 'ext.nsfilerepo.filepage' ] ).done( function () {
				var $me = $( me );
				me.dialog = new nsfr.ui.dialog.ChangeFileNamespaceAssociation( {
					unprefixedFileName: $me.data( 'unprefixedfilename' ),
					currentNS: $me.data( 'currentnamespace' ),
					excludeNS: $me.data( 'excludens' ),
					formattedNamespaces: mw.config.get( 'wgFormattedNamespaces' ),
					currentPage: mw.config.get( 'wgPageName' )
				} );
				me.dialog.on( 'move-filepage-complete', function ( oldPageName, newPageName ) {
					window.location.reload();
				} );
				me.dialog.show();
			} );
		}

		e.defaultPrevented = true;
		return false;
	} );
}( mediaWiki, jQuery, document ) );
