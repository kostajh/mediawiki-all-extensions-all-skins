Ext.define('BS.Bookshelf.data.BookStore', {
	extend: 'BS.store.BSApi',
	requires: [ 'BS.store.BSApi' ],

	constructor: function( cfg ) {
		cfg = cfg || {};

		this.callParent( [ Ext.merge( {
			fields: [
				'page_id', 'page_title', 'page_namespace',
				'book_first_chapter_prefixedtext', 'book_prefixedtext',
				'book_type', 'book_displaytext', 'book_meta',
				'book_first_chapter_link'
			]
		}, cfg, {
			apiAction: 'bs-bookshelf-store'
		} ) ] );

		this.on( 'beforeload', function( store ) {
			// Send data of temporary books to the store
			var proxy = store.getProxy();
			proxy.extraParams.tempBooks = JSON.stringify( this.getLocalBooks() );
			store.setProxy( proxy );
		}.bind( this ) );
	},

	getLocalBooks: function() {
		return bs.bookshelf.localBookRepo.getParsed();
	}
});
