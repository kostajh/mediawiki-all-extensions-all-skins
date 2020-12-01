( function ( mw, $, bs, d, undefined ) {
	var specialNamespace = ( mw.config.get( 'wgContentNamespaces' ).indexOf( mw.config.get( 'wgNamespaceNumber' ) ) === -1 );

	$( document ).on( 'click', '.bs-fa-new-page', function () {
		mw.loader.using( 'ext.bluespice.extjs' ).done( function () {
			Ext.Loader.setPath( 'BS.Calumma', mw.config.get( 'stylepath' )
					+ '/BlueSpiceCalumma/resources/js/BS.Calumma' );

			Ext.onReady( function () {
				var siteNamespace;
				if ( specialNamespace || mw.config.get( 'wgCanonicalNamespace' ) === '' ) {
					siteNamespace = '';
				} else {
					siteNamespace = mw.config.get( 'wgCanonicalNamespace' );
				}
				var dlg = Ext.create( 'BS.Calumma.dialog.NewPage', {
					id: "bs-calumma-featured-action-dialog-newpage",
					namespace: siteNamespace
				} );

				dlg.on( 'ok', function ( sender, data ) {
					if ( data.name ) {
						window.location.href = mw.util.getUrl( data.name, {
							action: 'edit',
							checkpagetemplates: 1
						} );
					}
				} );
				dlg.show();
			} );
		} );
	} );

	$( document ).on( 'click', '.bs-fa-new-subpage', function () {
		mw.loader.using( 'ext.bluespice.extjs' ).done( function () {
			Ext.Loader.setPath( 'BS.Calumma', mw.config.get( 'stylepath' )
					+ '/BlueSpiceCalumma/resources/js/BS.Calumma' );

			Ext.onReady( function () {
				var currentPageName = mw.config.get( 'wgPageName' );
				dlg = Ext.create( 'BS.Calumma.dialog.NewSubPage', {
					id: "bs-calumma-featured-action-dialog-newsubpage",
					basePage: currentPageName
				} );

				dlg.on( 'ok', function ( sender, data ) {
					var data = {
						namespace: mw.config.get( 'wgCanonicalNamespace' ),
						name: currentPageName + '/' + data
					};
					window.location.href = mw.util.getUrl( data.name, {
						action: 'edit',
						checkpagetemplates: 1
					} );
				} );
				dlg.show();
			} );
		} );
	} );

} )( mediaWiki, jQuery, blueSpice, document );
