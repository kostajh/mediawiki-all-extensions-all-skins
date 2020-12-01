ve.dm.NewbiesNode = function VeDmNewbiesNode() {
	// Parent constructor
	ve.dm.NewbiesNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.dm.NewbiesNode, ve.dm.MWInlineExtensionNode );

/* Static members */

ve.dm.NewbiesNode.static.name = 'newbies';

ve.dm.NewbiesNode.static.tagName = 'bs:newbies';

// Name of the parser tag
ve.dm.NewbiesNode.static.extensionName = 'bs:newbies';


// This tag renders without content
ve.dm.NewbiesNode.static.childNodeTypes = [];
ve.dm.NewbiesNode.static.isContent = false;


/* Registration */

ve.dm.modelRegistry.register( ve.dm.NewbiesNode );
