(function( mw, bs, undefined){
	var uploaderConfig = null;
	if( mw.config.get( 'bsEnableUploads' ) ) {
		uploaderConfig = {};
	};

	Ext.Loader.setPath(
		'BS.BlueSpiceExtendedFilelist',
		mw.config.get( 'wgScriptPath' ) +
			'/extensions/BlueSpiceExtendedFilelist/resources/BS.BlueSpiceExtendedFilelist'
	);

	Ext.onReady( function(){
		Ext.create ('BS.BlueSpiceExtendedFilelist.grid.FileRepo', {
			renderTo: 'bs-extendedfilelist-grid',
			uploaderCfg: uploaderConfig
		});
	});
}) (mediaWiki, blueSpice);