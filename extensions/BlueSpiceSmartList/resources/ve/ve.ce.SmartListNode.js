ve.ce.SmartListNode = function VeCeSmartListNode() {
	// Parent constructor
	ve.ce.SmartListNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ce.SmartListNode, ve.ce.MWInlineExtensionNode );

/* Static properties */

ve.ce.SmartListNode.static.name = 'smartlist';

ve.ce.SmartListNode.static.primaryCommandName = 'bs:smartlist';

// If body is empty, tag does not render anything
ve.ce.SmartListNode.static.rendersEmpty = true;

/**
 * @inheritdoc ve.ce.GeneratedContentNode
 */
ve.ce.SmartListNode.prototype.validateGeneratedContents = function ( $element ) {
	if ( $element.is( 'div' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
		return false;
	}
	return true;
};

/* Registration */

ve.ce.nodeFactory.register( ve.ce.SmartListNode );
