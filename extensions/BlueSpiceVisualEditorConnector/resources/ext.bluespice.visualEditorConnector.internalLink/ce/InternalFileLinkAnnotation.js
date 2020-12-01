bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.InternalFileLinkAnnotation = function() {
	// Parent InternalFileLinkAnnotation
	bs.vec.ce.InternalFileLinkAnnotation.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.InternalFileLinkAnnotation, ve.ce.MWInternalLinkAnnotation );

/* Static Properties */

bs.vec.ce.InternalFileLinkAnnotation.static.name = 'link/internalFile';

/* Registration */
ve.ce.annotationFactory.register( bs.vec.ce.InternalFileLinkAnnotation );
