( function( mw, $, bs ) {
	Ext.onReady( function() {
		Ext.Loader.setPath(
			'BS.NamespaceCSS',
			bs.em.paths.get( 'BlueSpiceNamespaceCSS' ) + '/resources/BS.NamespaceCSS'
		);
		Ext.require( 'BS.NamespaceCSS.panel.Manager', function(){
			new BS.NamespaceCSS.panel.Manager( {
				renderTo: 'bs-namespacecss-manager'
			});
		});
	});
})( mediaWiki, jQuery, blueSpice );