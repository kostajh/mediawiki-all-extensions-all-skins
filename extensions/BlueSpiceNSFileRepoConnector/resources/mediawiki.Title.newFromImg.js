(function( mw, $ ) {
	var oldTitleNewFromImg = mw.Title.newFromImg;

	mw.Title.newFromImg = function ( img ) {
		var title = oldTitleNewFromImg( img );
		var src = img.jquery ? img[0].src : img.src;
		src = decodeURI( src );
		var matches = src.match( /\/([0-9]*?)\/[a-f0-9]\/[a-f0-9]{2}\/([^\s]+)$/ );
		if( !matches ) {
			return title;
		}
		var nsId = parseInt( matches[1] );
		if( nsId ) {
			var realTitle = mw.Title.newFromText( title.getMainText(), nsId );
			//We use matches[2] as realTitle.getText() returns an first case upper string.
			//This is quite ugly, but there are cases in which the first char is not upper case
			var fileNameParts = matches[2].split('/');
			var fileName = fileNameParts[0];
			return mw.Title.newFromText(
				'File:' + realTitle.getNamespacePrefix() + fileName
			);
		}

		return title;
	};
})( mediaWiki, jQuery );