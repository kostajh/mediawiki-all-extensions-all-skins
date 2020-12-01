( function ( mw, bs, $ ) {
	bs.bookshelf.storageLocation.BookNamespace = function( cfg ) {
		bs.bookshelf.storageLocation.BookNamespace.parent.call( this, cfg );
	};

	OO.inheritClass( bs.bookshelf.storageLocation.BookNamespace, bs.bookshelf.storageLocation.WikiPage );

	bs.bookshelf.storageLocation.BookNamespace.prototype.allowChangingPageTags = function() {
		return true;
	};

	bs.bookshelf.storageLocation.BookNamespace.prototype.getLabel = function() {
		return mw.message( 'bs-bookshelf-grouping-template-type-ns_book' ).parse();
	};

	bs.bookshelf.storageLocation.BookNamespace.prototype.getEditUrlFromTitle = function( bookTitle, params ) {
		params = params || {};
		return new mw.Title( bookTitle, bs.ns.NS_BOOK ).getUrl( $.extend( {
			action: 'edit'
		}, params ) );
	};

	bs.bookshelf.storageLocationRegistry.register( 'ns_book', new bs.bookshelf.storageLocation.BookNamespace() );
} ) ( mediaWiki, blueSpice, jQuery );
