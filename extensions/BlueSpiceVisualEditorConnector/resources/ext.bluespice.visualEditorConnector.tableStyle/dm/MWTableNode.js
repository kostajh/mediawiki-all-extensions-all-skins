bs.util.registerNamespace( 'bs.vec.dm' );

bs.vec.dm.MWTableNode = function BsVecDmMWTableNode() {
	// Parent constructor
	bs.vec.dm.MWTableNode.super.apply( this, arguments );
};

/* Inheritance */
OO.inheritClass( bs.vec.dm.MWTableNode, ve.dm.MWTableNode );

bs.vec.dm.MWTableNode.static.toDataElement = function ( domElements ) {
	var dataElement = ve.dm.MWTableNode.static.toDataElement( domElements ),
		styleParser = new bs.vec.util.StyleAttributeParser( domElements[0].getAttribute( 'style' ) || '' );

	if ( styleParser.getValueForAttr( 'width' ) !== null && dataElement.hasOwnProperty( 'attributes' ) ) {
		dataElement.attributes.tablewidth = styleParser.getValueForAttr( 'width' );
	}

	return dataElement;
};

bs.vec.dm.MWTableNode.static.toDomElements = function ( dataElement, doc ) {
	var elements = ve.dm.MWTableNode.static.toDomElements( dataElement, doc ),
		element = elements[0],
		attributes = dataElement.attributes || null,
		styleParser = new bs.vec.util.StyleAttributeParser( element.getAttribute( 'style' ) || '' );

	if ( !attributes || !attributes.tablewidth ) {
		return [ element ];
	}

	if ( attributes.tablewidth ) {
		styleParser.addToStyle( 'width', attributes.tablewidth );
	} else {
		styleParser.removeFromStyle( 'width' );
	}

	element.setAttribute( 'style', styleParser.toString() );

	return [ element ];
};


ve.dm.modelRegistry.register( bs.vec.dm.MWTableNode );
