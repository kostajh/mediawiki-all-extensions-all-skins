bs.util.registerNamespace( 'bs.vec.ce' );

bs.vec.ce.SoftHyphenNode = function() {
	// Parent constructor
	bs.vec.ce.SoftHyphenNode.super.apply( this, arguments );

	this.$element.addClass( 'bs-soft-hyphen-node' );
};

/* Inheritance */
OO.inheritClass( bs.vec.ce.SoftHyphenNode, ve.ce.MWEntityNode );

/* Static Properties */

bs.vec.ce.SoftHyphenNode.static.name = 'softHyphen';

/* Registration */

ve.ce.nodeFactory.register( bs.vec.ce.SoftHyphenNode );
