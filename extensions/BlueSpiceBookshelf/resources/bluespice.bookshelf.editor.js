Ext.onReady( function(){
	Ext.Loader.setPath(
		'BS.Bookshelf',
		bs.em.paths.get( 'BlueSpiceBookshelf' ) + '/resources/BS.Bookshelf'
	);
	var oConfig = mw.config.get('bsBookshelfData');
	oConfig.renderTo = 'bs-bookshelf-editorpanel';
	Ext.create( 'BS.Bookshelf.panel.BookEditor', oConfig );
} );
