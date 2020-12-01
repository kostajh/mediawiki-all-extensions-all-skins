bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.MWTableNode = function BsVecCeMWTableNode() {
	// Parent constructor
	bs.vec.ce.MWTableNode.super.apply( this, arguments );

	this.model.connect( this, {
		attributeChange: 'updateWidth'
	} );
};

OO.inheritClass( bs.vec.ce.MWTableNode, ve.ce.MWTableNode );

bs.vec.ce.MWTableNode.prototype.updateWidth = function () {
	if ( this.model.element.attributes.hasOwnProperty( 'tablewidth' ) ) {
		var width = this.model.element.attributes.tablewidth,
			styleParser = new bs.vec.util.StyleAttributeParser( this.$element.attr( 'style' ) || '' );

		if ( width ) {
			styleParser.addToStyle( 'width', width );
		} else {
			styleParser.removeFromStyle( 'width' );
		}
		this.$element.attr( 'style', styleParser.toString() );
	}
};

ve.ce.nodeFactory.register( bs.vec.ce.MWTableNode );
