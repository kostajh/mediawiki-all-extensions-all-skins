bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.InternalMediaLinkAnnotation = function() {
	// Parent InternalMediaLinkAnnotation
	bs.vec.ce.InternalMediaLinkAnnotation.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.InternalMediaLinkAnnotation, ve.ce.MWInternalLinkAnnotation );

/* Static Properties */

bs.vec.ce.InternalMediaLinkAnnotation.static.name = 'link/internalMedia';

/* Registration */
ve.ce.annotationFactory.register( bs.vec.ce.InternalMediaLinkAnnotation );
