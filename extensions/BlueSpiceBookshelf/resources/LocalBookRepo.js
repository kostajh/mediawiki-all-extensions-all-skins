( function ( mw, bs, $ ) {
	bs.bookshelf.LocalBookRepo = function() {
		this.parsed = null;
	};

	OO.initClass( bs.bookshelf.LocalBookRepo);

	bs.bookshelf.LocalBookRepo.prototype.hasBook = function( bookTitle ) {
		return this.getParsed().hasOwnProperty( bookTitle );
	};

	bs.bookshelf.LocalBookRepo.prototype.getBook = function( bookTitle ) {
		if ( this.hasBook( bookTitle ) ) {
			return this.getParsed()[bookTitle];
		}

		return null;
	};

	bs.bookshelf.LocalBookRepo.prototype.saveBook = function( bookTitle, content ) {
		var books = this.getParsed();
		books[bookTitle] = content;
		localStorage.setItem( 'bookshelfLocalBooks', JSON.stringify( books ) );
		this.reparse();

		return true;
	};

	bs.bookshelf.LocalBookRepo.prototype.appendToBook = function( bookTitle, content ) {
		var books = this.getParsed(),
			existingContent = books[bookTitle] || '';

		return this.saveBook( bookTitle, existingContent + '\n' + content );
	};

	bs.bookshelf.LocalBookRepo.prototype.deleteBook = function( bookTitle ) {
		var books = this.getParsed();
		if ( $.isEmptyObject( books ) ) {
			return false;
		}
		if ( books.hasOwnProperty( bookTitle ) ) {
			delete books[bookTitle];
		}
		localStorage.setItem( 'bookshelfLocalBooks', JSON.stringify( books ) );

		return true;
	};

	bs.bookshelf.LocalBookRepo.prototype.reparse = function() {
		this.getParsed( true );
	};

	bs.bookshelf.LocalBookRepo.prototype.getParsed = function( reparse ) {
		reparse = reparse || false;
		if ( reparse || !this.parsed ) {
			var blob = localStorage.getItem( 'bookshelfLocalBooks' );
			if ( !blob ) {
				this.parsed = {};
			} else {
				this.parsed = JSON.parse( blob );
			}
		}

		return this.parsed;
	};

	bs.bookshelf.localBookRepo = new bs.bookshelf.LocalBookRepo();

} ) ( mediaWiki, blueSpice, jQuery );
