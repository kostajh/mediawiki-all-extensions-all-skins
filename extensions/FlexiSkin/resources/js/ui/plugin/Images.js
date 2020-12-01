flexiskin.ui.plugin.Images = function () {
	flexiskin.ui.plugin.Images.parent.call( this );
};

OO.inheritClass( flexiskin.ui.plugin.Images, flexiskin.ui.plugin.Plugin );

flexiskin.ui.plugin.Images.prototype.provideControls = function () {
	return {
		images: {
			label: mw.message( 'flexiskin-ui-plugin-images-label' ).text(),
			expanded: true,
			items: {
				logo: {
					label: mw.message( 'flexiskin-ui-plugin-images-logo-label' ).text(),
					widget: new OO.ui.SelectFileWidget( {
						showDropTarget: true
					} ),
					actionCallback: {
						preview: this.onPreview,
						save: this.onSave,
						setValue: this.onSetValue
					}
				},
				favicon: {
					label: mw.message( 'flexiskin-ui-plugin-images-favicon-label' ).text(),
					widget: new OO.ui.SelectFileWidget( {
						showDropTarget: true
					} ),
					actionCallback: {
						preview: this.onPreview,
						save: this.onSave,
						setValue: this.onSetValue
					}
				}
			}
		}
	};
};

flexiskin.ui.plugin.Images.prototype.onPreview = function ( data, itemId ) {
	var dfd = $.Deferred(),
		file;

	// Get currently choosen file
	file = this.getValue();
	if ( !( file instanceof File ) ) {
		// Nothing selected
		return dfd.reject();
	}

	// After file is applied reject the promise, since we dont want any
	// data sent to the backend
	var reader = new FileReader();
	reader.readAsDataURL( file );
	reader.onloadend = function () {
		var b64 = reader.result;
		if ( itemId === 'images/logo' ) {
			$( '#p-logo a.mw-wiki-logo' ).css( 'background-image', 'url("' + b64 + '")' );
		}

		if ( itemId === 'images/favicon' ) {
			$( 'link[rel="shortcut icon"]' ).attr( 'href', 'data:' + file.type + ';' + b64 );
		}

		dfd.reject();
	};

	return dfd.promise();
};

flexiskin.ui.plugin.Images.prototype.onSave = function ( data, itemId ) {
	function compareFile( a, b ) {
		// No really good way to compare files
		return a.name === b.name && a.size === b.size && a.type === b.type;
	}

	function getUploadData( filename, file ) {
		return {
			filename: filename,
			fileObject: {
				name: file.name,
				size: file.size,
				type: file.type
			}
		};
	}

	var dfd = $.Deferred(),
		file,

	 nameForFile = itemId.replace( '/', '-' );
	// Get currently choosen file
	file = this.getValue();
	if ( !( file instanceof File ) ) {
		// Nothing selected
		return dfd.reject();
	}

	// Check if its dirty, if not just resolve to existing file
	var oldData = this.getData();
	if ( oldData && oldData.hasOwnProperty( 'file' ) && compareFile( oldData.file, file ) ) {
		dfd.resolve( getUploadData( oldData.filename, file ) );
	} else {
		// If file is actually new, upload it
		new mw.Api().upload( file, {
			filename: 'flexiskin-' + nameForFile + '.' + file.name.split( '.' ).pop(),
			ignorewarnings: 1
		} ).done( function ( response ) {
			if ( response.hasOwnProperty( 'upload' ) ) {
				if ( response.upload.result === 'Success' ) {
					dfd.resolve( getUploadData( response.upload.filename, file ) );
				} else {
					dfd.reject();
				}
			}
		} ).fail( function ( error, result ) {
			if ( error === 'exists' || error === 'duplicate' && result.hasOwnProperty( 'upload' ) ) {
				dfd.resolve( getUploadData( result.upload.filename, file ) );
			}
			// Cannot upload file
			dfd.reject();
		} );
	}

	return dfd.promise();
};

flexiskin.ui.plugin.Images.prototype.onSetValue = function ( data, itemId ) {
	data = data || {};

	if ( $.type( data ) === 'object' && data.hasOwnProperty( 'url' ) ) {
		// Get the file object from the URL of the file and set appropriate values on widget
		fetch( data.url ).then( function ( fRes ) {
			fRes.blob().then( function ( blob ) {
				var file = new File( [ blob ], data.fileObject.name, data.fileObject );
				this.setValue( [ file ] );
				this.setData( $.extend( this.getData() || {}, { filename: data.filename, file: data.fileObject } ) );
			}.bind( this ) );
		}.bind( this ) );
	}
};

flexiskin.registry.Plugin.register( 'images', flexiskin.ui.plugin.Images );
