Ext.onReady( function(){
	Ext.Loader.setPath(
		'BS.Bookshelf',
		bs.em.paths.get( 'BlueSpiceBookshelf' ) + '/resources/BS.Bookshelf'
	);
	var oConfig = mw.config.get( 'bsBookshelfData' );
	oConfig.renderTo = 'bs-bookshelf-view';
	Ext.create( 'BS.Bookshelf.panel.BookView', oConfig );
} );
