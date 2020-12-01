$(document).on( 'click', '#ca-bookshelf-add-to-book', function( e ) {
	mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
		Ext.require( 'BS.Bookshelf.dialog.AddToBook', function() {
			var dlg = new BS.Bookshelf.dialog.AddToBook();
			dlg.setData( mw.config.get( 'wgPageName' ) );
			dlg.show();
		});
	});
	e.preventDefault();
	return false;
});