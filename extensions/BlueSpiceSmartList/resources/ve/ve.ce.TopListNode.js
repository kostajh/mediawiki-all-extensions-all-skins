ve.ce.TopListNode = function VeCeTopListNode() {
	// Parent constructor
	ve.ce.TopListNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ce.TopListNode, ve.ce.MWInlineExtensionNode );

/* Static properties */

ve.ce.TopListNode.static.name = 'toplist';

ve.ce.TopListNode.static.primaryCommandName = 'bs:toplist';

// If body is empty, tag does not render anything
ve.ce.TopListNode.static.rendersEmpty = true;

/**
 * @inheritdoc ve.ce.GeneratedContentNode
 */
ve.ce.TopListNode.prototype.validateGeneratedContents = function ( $element ) {
	if ( $element.is( 'div' ) && $element.children( '.bsErrorFieldset' ).length > 0 ) {
		return false;
	}
	return true;
};

/* Registration */

ve.ce.nodeFactory.register( ve.ce.TopListNode );
