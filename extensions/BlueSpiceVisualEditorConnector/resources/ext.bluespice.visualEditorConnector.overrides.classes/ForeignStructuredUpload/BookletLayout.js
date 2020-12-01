bs.util.registerNamespace( 'bs.vec.ui.ForeignStructuredUpload' );

bs.vec.ui.ForeignStructuredUpload.BookletLayout = function BsVecUiForeignStructuredUploadBookletLayout( config ) {
	bs.vec.ui.ForeignStructuredUpload.BookletLayout.parent.call( this, config );

	this.saveCalledInit = true;
};

OO.inheritClass( bs.vec.ui.ForeignStructuredUpload.BookletLayout, mw.ForeignStructuredUpload.BookletLayout );

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.getFilename = function() {
	var filename = bs.vec.ui.ForeignStructuredUpload.BookletLayout.parent.prototype.getFilename.call( this );

	var data = {
		filename: filename
	}
	this.emit( 'getfilename', this, data );

	return data.filename;
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.createUpload = function () {
	return new bs.vec.ui.ForeignStructuredUpload.ForeignStructuredUpload( this.target, {
		parameters: {
			errorformat: 'html',
			errorlang: mw.config.get( 'wgUserLanguage' ),
			errorsuselocal: 1,
			formatversion: 2
		}
	} );
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.renderUploadForm = function () {
	var form = bs.vec.ui.ForeignStructuredUpload.BookletLayout.super.prototype.renderUploadForm.apply( this );
	var items = form.getItems();
	var fieldlayouts = items[0].getItems();
	// Hide "upload-form-label-not-own-work-message-generic-local" text
	fieldlayouts[2].$element.hide();
	return form;
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.saveFile = function() {
	if ( this.saveCalledInit === false ) {
		this.uploadPromise = this.upload.uploadToStash( true );
	}
	var layout = this,
		deferred = $.Deferred();

	this.upload.setFilename( this.getFilename() );
	this.upload.setText( this.getText() );

	this.uploadPromise.then( function () {
		layout.upload.finishStashUpload( {
			ignorewarnings: layout.saveCalledInit === false
		} ).then( function () {
			var name;

			// Normalize page name and localise the 'File:' prefix
			name = new mw.Title( 'File:' + layout.upload.getFilename() ).toString();
			layout.filenameUsageWidget.setValue( '[[' + name + ']]' );
			layout.setPage( 'insert' );

			deferred.resolve();
			layout.emit( 'fileSaved', layout.upload.getImageInfo() );
		}, function () {
			layout.getErrorMessageForStateDetails().then( function ( errorMessage ) {
				deferred.reject( errorMessage );
			} );
		} );
		layout.saveCalledInit = false;
	} );

	return deferred.promise();
};

bs.vec.ui.ForeignStructuredUpload.BookletLayout.prototype.getErrorMessageForStateDetails = function () {
	var state = this.upload.getState(),
		stateDetails = this.upload.getStateDetails(),
		warnings = stateDetails.upload && stateDetails.upload.warnings;

	if ( state === mw.Upload.State.WARNING ) {
		if ( warnings.exists !== undefined ) {
			return $.Deferred().resolve( new OO.ui.Error(
				$( '<p>' ).msg( 'fileexists', 'File:' + warnings.exists ),
				{ recoverable: true, warning: true }
			) );
		} else if ( warnings[ 'duplicate-archive' ] !== undefined ) {
			return $.Deferred().resolve( new OO.ui.Error(
				$( '<p>' ).msg( 'file-deleted-duplicate', 'File:' + warnings[ 'duplicate-archive' ] ),
				{ recoverable: true, warning: true }
			) );
		} else if ( warnings[ 'was-deleted' ] !== undefined ) {
			return $.Deferred().resolve( new OO.ui.Error(
				$( '<p>' ).msg( 'filewasdeleted', 'File:' + warnings[ 'was-deleted' ] ),
				{ recoverable: true,warning: true }
			) );
		}
	}
	return bs.vec.ui.ForeignStructuredUpload.BookletLayout.parent.prototype.getErrorMessageForStateDetails.call( this );
};
