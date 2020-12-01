( function ( mw, bs, $ ) {
	bs.bookshelf.storageLocation.UserBook = function( cfg ) {
		bs.bookshelf.storageLocation.UserBook.parent.call( this, cfg );

		this.userName = mw.config.get( 'wgUserName' );
	};

	OO.inheritClass( bs.bookshelf.storageLocation.UserBook, bs.bookshelf.storageLocation.WikiPage );

	bs.bookshelf.storageLocation.UserBook.prototype.allowChangingPageTags = function() {
		return false;
	};

	bs.bookshelf.storageLocation.UserBook.prototype.getLabel = function() {
		return mw.message( 'bs-bookshelf-grouping-template-type-user_book' ).parse();
	};

	bs.bookshelf.storageLocation.UserBook.prototype.getEditUrlFromTitle = function( bookTitle, params ) {
		params = params || {};
		var title = new mw.Title( bookTitle, bs.ns.NS_USER );

		return title.getUrl( $.extend( {
			action: 'edit'
		}, params ) );
	};

	bs.bookshelf.storageLocationRegistry.register( 'user_book', new bs.bookshelf.storageLocation.UserBook() );
} ) ( mediaWiki, blueSpice, jQuery );
