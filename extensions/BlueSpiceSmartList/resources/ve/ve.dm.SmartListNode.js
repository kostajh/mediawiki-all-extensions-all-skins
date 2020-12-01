ve.dm.SmartListNode = function VeDmSmartListNode() {
	// Parent constructor
	ve.dm.SmartListNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.dm.SmartListNode, ve.dm.MWInlineExtensionNode );

/* Static members */

ve.dm.SmartListNode.static.name = 'smartlist';

ve.dm.SmartListNode.static.tagName = 'bs:smartlist';

// Name of the parser tag
ve.dm.SmartListNode.static.extensionName = 'bs:smartlist';

// This tag renders without content
ve.dm.SmartListNode.static.childNodeTypes = [];
ve.dm.SmartListNode.static.isContent = false;

/* Registration */

ve.dm.modelRegistry.register( ve.dm.SmartListNode );
