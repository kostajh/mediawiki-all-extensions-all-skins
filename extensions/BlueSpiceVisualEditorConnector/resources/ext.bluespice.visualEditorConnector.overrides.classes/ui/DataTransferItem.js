
/** Override normal VE DataTranferItem (for files) */
ve.ui.DataTransferItem.static.newFromItem = function ( item, htmlStringData ) {
	return new bs.vec.ui.DataTransferItem(
		item.kind, item.type,
		{ item: item, htmlStringData: htmlStringData }, item.getAsFile().name
	);
};

bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.DataTransferItem = function( kind, type, data, name ) {
	bs.vec.ui.DataTransferItem.parent.call( this, kind, type, data, name );
};

OO.inheritClass( bs.vec.ui.DataTransferItem, ve.ui.DataTransferItem );

bs.vec.ui.DataTransferItem.prototype.getAsFile = function () {
	if ( this.data.item ) {
		var blob = this.data.item.getAsFile();
		if ( blob instanceof File ) {
			return this.getCustomNameFile( blob );

		}
		return blob;
	}

	return bs.vec.ui.DataTransferItem.parent.prototype.getAsFile();
};

bs.vec.ui.DataTransferItem.prototype.getCustomNameFile = function ( blob ) {
	return new File(
		[blob],
		this.getFormattedFileName(),
		{ type: blob.type }
	);
};

bs.vec.ui.DataTransferItem.prototype.getFormattedFileName = function () {
	var filename = bs.vec.config.get( 'PasteFilename' );
	if ( !filename ) {
		return this.name;
	}

	filename = this.substitute( filename );

	var extension = this.getExtension();
	return [ filename, extension ].join( '.' );
};

bs.vec.ui.DataTransferItem.prototype.substitute = function ( filename ) {
	var regex = /{{(.*?)}}/gi,
		matches;

	do {
		matches = regex.exec( filename );
		if ( matches ) {
			var key = matches[1];
			switch ( key ) {
				case 'pagename':
					filename = filename.replace( matches[0], this.getEscapedPageName() );
					break;
				case 'timestamp':
					filename = filename.replace( matches[0], Date.now() );
					break;
				case 'random':
					filename = filename.replace(
						matches[0],
						Math.floor( Math.random() * ( 999999 - 999 + 1 ) ) + 999
					);
					break;
			}
		}
	} while ( matches );

	return filename;
};

bs.vec.ui.DataTransferItem.prototype.getEscapedPageName = function () {
	var pagename = mw.config.get( 'wgPageName' );
	return pagename.replace( ':', '-' ).replace( '/', '-' );
};
