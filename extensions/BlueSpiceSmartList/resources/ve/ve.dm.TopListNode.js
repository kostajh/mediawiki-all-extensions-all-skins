ve.dm.TopListNode = function VeDmTopListNode() {
	// Parent constructor
	ve.dm.TopListNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.dm.TopListNode, ve.dm.MWInlineExtensionNode );

/* Static members */

ve.dm.TopListNode.static.name = 'toplist';

ve.dm.TopListNode.static.tagName = 'bs:toplist';

// Name of the parser tag
ve.dm.TopListNode.static.extensionName = 'bs:toplist';

// This tag renders without content
ve.dm.TopListNode.static.childNodeTypes = [];
ve.dm.TopListNode.static.isContent = false;

/* Registration */

ve.dm.modelRegistry.register( ve.dm.TopListNode );
