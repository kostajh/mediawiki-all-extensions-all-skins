bs.util.registerNamespace( 'bs.insertlink.dm' );

bs.insertlink.dm.ExternalFileLinkAnnotation = function() {
	// Parent constructor
	bs.insertlink.dm.ExternalFileLinkAnnotation.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.insertlink.dm.ExternalFileLinkAnnotation, ve.dm.MWExternalLinkAnnotation );

/* Static Properties */

bs.insertlink.dm.ExternalFileLinkAnnotation.static.name = 'link/fileExternal';

bs.insertlink.dm.ExternalFileLinkAnnotation.static.matchTagNames = [ 'a' ];
bs.insertlink.dm.ExternalFileLinkAnnotation.static.matchFunction = function( domElement ) {
	return bs.insertlink.widget.LocalFilePathInputWidget.static.checkAndPrefix(
		domElement.getAttribute( 'href' )
	) !== false;
};

bs.insertlink.dm.ExternalFileLinkAnnotation.static.getHref = function ( dataElement ) {
	return dataElement.attributes.href;
};

/* Registration */

ve.dm.modelRegistry.register( bs.insertlink.dm.ExternalFileLinkAnnotation );
