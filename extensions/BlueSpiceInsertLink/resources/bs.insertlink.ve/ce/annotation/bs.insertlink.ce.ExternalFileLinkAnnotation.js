bs.util.registerNamespace( 'bs.insertlink.ce' );

bs.insertlink.ce.ExternalFileLinkAnnotation = function() {
	bs.insertlink.ce.ExternalFileLinkAnnotation.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.insertlink.ce.ExternalFileLinkAnnotation, ve.ce.MWExternalLinkAnnotation );

/* Static Properties */

bs.insertlink.ce.ExternalFileLinkAnnotation.static.name = 'link/fileExternal';

/* Registration */
ve.ce.annotationFactory.register( bs.insertlink.ce.ExternalFileLinkAnnotation );
