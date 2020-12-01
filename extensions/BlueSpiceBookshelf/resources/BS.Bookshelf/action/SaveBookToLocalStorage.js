Ext.define( 'BS.Bookshelf.action.SaveBookToLocalStorage', {
	extend: 'BS.action.Base',
	bookTitle: '',
	content: '',

	execute: function() {
		var dfd = $.Deferred();

		this.actionStatus = BS.action.Base.STATUS_RUNNING;

		bs.bookshelf.localBookRepo.saveBook( this.bookTitle, this.content );
		this.actionStatus = BS.action.Base.STATUS_DONE;
		dfd.resolve( this );

		return dfd.promise();
	},

	getDescription: function() {
		return mw.message( 'bs-bookshelf-action-save-to-local-storage' ).text();
	}
} );
