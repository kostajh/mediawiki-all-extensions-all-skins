( function ( mw, bs, $, undefined ) {
	mw.loader.using( ['ext.bluespice.extjs', 'ext.bluespice'] ).done( function() {
		Ext.onReady( function(){
			var basePath = mw.config.get( "wgExtensionAssetsPath" );
			Ext.Loader.setPath(
				'ConfigManager',
				basePath + '/ConfigManager/resources/ConfigManager'
			);
			Ext.create( 'ConfigManager.panel.Manager', {
				renderTo: 'configmanager',
				height: 1
			});
		});
	});
}( mediaWiki, blueSpice, jQuery ) );
