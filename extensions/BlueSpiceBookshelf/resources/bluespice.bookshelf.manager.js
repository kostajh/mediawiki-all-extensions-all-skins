(function( mw ) {
	var config = mw.config.get('bsBookshelfBookManagerConfig');
	mw.loader.using( config.dependencies ).done(function(){
		Ext.onReady(function(){
			Ext.Loader.setPath(
				'BS.Bookshelf',
				bs.em.paths.get( 'BlueSpiceBookshelf' ) + '/resources/BS.Bookshelf'
			);
			Ext.create( 'BS.Bookshelf.panel.BookManager', {
				renderTo: 'bs-bookshelf-managerpanel'
			});
		});
	});
})( mediaWiki );
