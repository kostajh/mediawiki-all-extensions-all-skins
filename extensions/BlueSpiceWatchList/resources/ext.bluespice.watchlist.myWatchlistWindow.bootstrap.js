( function ( mw, $, d, undefined ) {
	$( d ).on( 'click', '#pt-watchlist', function ( e ) {
		e.defaultPrevented = true;

		mw.loader.using( [ 'ext.bluespice.extjs', 'ext.bluespice.watchlist.myWatchlistWindow' ] )
		.done( function () {
			Ext.onReady( function () {
				var win = Ext.create( 'BS.WatchList.window.MyWatchlist', {} );
				win.show();
			} );
		} );

		return false;
	} );
} )( mediaWiki, jQuery, document );
