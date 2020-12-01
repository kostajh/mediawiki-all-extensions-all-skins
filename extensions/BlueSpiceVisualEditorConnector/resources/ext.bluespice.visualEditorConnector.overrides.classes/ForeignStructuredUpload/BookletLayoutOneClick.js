bs.util.registerNamespace( 'bs.vec.ui.ForeignStructuredUpload' );

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick = function BsVecUiForeignStructuredUploadBookletLayoutOneClick( config ) {
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick, bs.vec.ui.ForeignStructuredUpload.BookletLayoutSimple );

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.prototype.setPage = function( bookletpagename ) {
	if( bookletpagename === 'info' ) {
		return;
	}
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.prototype.setPage.apply( this, [ bookletpagename ] );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.prototype.setFilename = function( filename ) {
	// Generate random name based on orig name
	var nameBits = filename.split( '.' );
	var extension = nameBits.pop();
	nameBits.push( new Date().getTime() );
	nameBits.push( extension );
	var randomName = nameBits.join( '.' );
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.prototype.setFilename.apply( this, [ randomName ] );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.prototype.uploadFile = function() {
	var dfd = $.Deferred();
	bs.vec.ui.ForeignStructuredUpload.BookletLayoutOneClick.parent.prototype.uploadFile.call( this ).done( function() {
		this.saveFile().then( function() {
			dfd.resolve();
		}.bind( this ), function( error ) {
			dfd.reject( error );
		} );
	}.bind( this ) ).fail( function() {
		dfd.reject();
	} );
	return dfd;
};