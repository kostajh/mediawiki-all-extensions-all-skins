bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.ColorAnnotation = function() {
	// Parent constructor
	bs.vec.ce.ColorAnnotation.super.apply( this,  arguments );

	// DOM changes
	this.$element
		.addClass( 've-ce-textStyleAnnotation' )
		.addClass( 've-ce-colorAnnotation' )
		.addClass( 've-ce-bidi-isolate' )
		.prop( {
			title: this.constructor.static.getDescription( this.model )
		} );

	if ( this.model.colorData.hasOwnProperty( 'class' ) ) {
		this.$element.addClass( this.model.colorData.class );
	} else if( this.model.colorData.hasOwnProperty( 'code' ) ) {
		this.$element.css( 'color', this.model.colorData.code );
	}
};

/* Inheritance */

OO.inheritClass( bs.vec.ce.ColorAnnotation, ve.ce.Annotation );

/* Static Properties */

bs.vec.ce.ColorAnnotation.static.name = 'textStyle/color';

bs.vec.ce.ColorAnnotation.static.tagName = 'span';

/* Static Methods */

/**
 * @inheritdoc
 */
bs.vec.ce.ColorAnnotation.static.getDescription = function ( model ) {
	return OO.ui.deferMsg( "bs-visualeditorconnector-color-annotation-desc" );
};

/* Registration */

ve.ce.annotationFactory.register( bs.vec.ce.ColorAnnotation );
