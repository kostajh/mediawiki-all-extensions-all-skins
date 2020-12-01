bs.util.registerNamespace( 'bs.vec.ui.ForeignStructuredUpload' );

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload = function( target, apiconfig ) {
	bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.parent.call( this, target, apiconfig );
};

OO.inheritClass( bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload, mw.ForeignStructuredUpload );

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.prototype.finishStashUpload = function( data ) {
	data = data || {};
	var upload = this,
		ignorewarnings = data.ignorewarnings || false;

	if ( !this.stashPromise ) {
		return $.Deferred().reject( 'This upload has not been stashed, please upload it to the stash first.' );
	}

	return this.stashPromise.then( function ( finishStash ) {
		upload.setState( mw.Upload.State.UPLOADING );

		return finishStash( {
			watchlist: ( upload.getWatchlist() ) ? 1 : undefined,
			comment: upload.getComment(),
			filename: upload.getFilename(),
			text: upload.getText(),
			ignorewarnings: ignorewarnings
		} ).then( function ( result ) {
			return upload.returnUpload.call( upload, result );
		}, function ( errorCode, result ) {
			if ( result && result.upload && result.upload.warnings ) {
				if ( ignorewarnings ) {
					return upload.returnUpload.call( upload, result );
				}
				upload.setState( mw.Upload.State.WARNING, result );
			} else {
				upload.setState( mw.Upload.State.ERROR, result );
			}
			return $.Deferred().reject( errorCode, result );
		} );
	} );
};

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.prototype.returnUpload = function( result ) {
	this.setState( mw.Upload.State.UPLOADED );

	this.imageinfo = result.upload.imageinfo;
	return result;
}

bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload.prototype.uploadToStash = function( ignorewarnings ) {
	ignorewarnings = ignorewarnings || false;
	var upload = this;

	if ( !this.getFile() ) {
		return $.Deferred().reject( 'No file to upload. Call setFile to add one.' );
	}

	if ( !this.getFilename() ) {
		this.setFilenameFromFile();
	}

	this.setState( mw.Upload.State.UPLOADING );

	this.stashPromise = this.api.chunkedUploadToStash( this.getFile(), {
		filename: this.getFilename(),
		ignorewarnings: ignorewarnings
	} ).then( function ( finishStash ) {
		upload.setState( mw.Upload.State.STASHED );
		return finishStash;
	}, function ( errorCode, result ) {
		if ( result && result.upload && result.upload.warnings ) {
			upload.setState( mw.Upload.State.WARNING, result );
		} else {
			upload.setState( mw.Upload.State.ERROR, result );
		}
		return $.Deferred().reject( errorCode, result );
	} );

	return this.stashPromise;
}

$.extend( mw.Api.prototype, {
	chunkedUploadToStash: function ( file, data, chunkSize, chunkRetries ) {
		var promise;

		if ( !data.filename ) {
			throw new Error( 'Filename not included in file data.' );
		}

		promise = this.chunkedUpload(
			file,
			$.extend( { stash: true }, data ),
			chunkSize,
			chunkRetries
		);

		return this.finishUploadToStash( promise, data );
	}
} );
