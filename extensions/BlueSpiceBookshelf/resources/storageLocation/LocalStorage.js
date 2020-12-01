( function ( mw, bs, $ ) {
	bs.bookshelf.storageLocation.LocalStorage = function( cfg ) {};

	OO.initClass( bs.bookshelf.storageLocation.LocalStorage);

	bs.bookshelf.storageLocation.LocalStorage.prototype.allowChangingPageTags = function() {
		return false;
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.getLabel = function() {
		return mw.message( 'bs-bookshelf-grouping-template-type-local_storage' ).parse();
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.appendText = function( record, content ) {
		var title = record.get( 'book_prefixedtext' ),
			dfd = $.Deferred();

		if ( bs.bookshelf.localBookRepo.appendToBook( title, content ) ) {
			dfd.resolve();
		} else {
			dfd.reject();
		}

		return dfd.promise();
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.getEditUrlFromTitle = function( bookTitle ) {
		return new mw.Title( 'BookshelfBookEditor', bs.ns.NS_SPECIAL ).getUrl( {
			book: bookTitle,
			type: 'local_storage',
			content: bs.bookshelf.localBookRepo.getBook( bookTitle )
		} );
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.getSaveAction = function( title, content ) {
		return new BS.Bookshelf.action.SaveBookToLocalStorage( {
			bookTitle: title,
			content: content
		} );
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.getBookPageText = function( data ) {
		var dfd = $.Deferred();

		setTimeout( function () {
			// This is needed because of some weird issue in batch action dialog
			// It wont start if data is returned immediately
			dfd.resolve( bs.bookshelf.localBookRepo.getBook( data.book_prefixedtext ) || '' );
		}, 200 );

		return dfd.promise();
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.getBookPageTextForTitle = function( title ) {
		return bs.bookshelf.localBookRepo.getBook( title ) || '';
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.delete = function( record ) {
		var dfd = $.Deferred(),
			bookName = record.get( 'book_prefixedtext' ),
			res = bs.bookshelf.localBookRepo.deleteBook( bookName );

		if ( res ) {
			dfd.resolve( true, {} );
		} else {
			dfd.reject();
		}

		return dfd.promise();
	};

	bs.bookshelf.storageLocation.LocalStorage.prototype.isTitleBased = function() {
		return false;
	};

	bs.bookshelf.storageLocationRegistry.register( 'local_storage', new bs.bookshelf.storageLocation.LocalStorage() );
} ) ( mediaWiki, blueSpice, jQuery );
