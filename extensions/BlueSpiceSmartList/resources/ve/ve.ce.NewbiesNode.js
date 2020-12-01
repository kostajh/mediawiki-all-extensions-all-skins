ve.ce.NewbiesNode = function VeCeNewbiesNode() {
	// Parent constructor
	ve.ce.NewbiesNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ce.NewbiesNode, ve.ce.MWInlineExtensionNode );

/* Static properties */

ve.ce.NewbiesNode.static.name = 'newbies';

ve.ce.NewbiesNode.static.primaryCommandName = 'bs:newbies';

// If body is empty, tag does not render anything
ve.ce.NewbiesNode.static.rendersEmpty = true;

/**
 * @inheritdoc ve.ce.GeneratedContentNode
 */
ve.ce.NewbiesNode.prototype.validateGeneratedContents = function ( $element ) {
	if ( $element.is( 'div' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
		return false;
	}
	return true;
};

/* Registration */

ve.ce.nodeFactory.register( ve.ce.NewbiesNode );
