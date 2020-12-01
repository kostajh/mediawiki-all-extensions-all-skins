bs.util.registerNamespace( 'bs.vec.tool' );

bs.vec.tool.InsertSoftHyphen = function ( toolGroup, config ) {
	bs.vec.tool.InsertSoftHyphen.super.call( this, toolGroup, config );
};
OO.inheritClass( bs.vec.tool.InsertSoftHyphen, ve.ui.FragmentWindowTool );
bs.vec.tool.InsertSoftHyphen.static.name = 'bsInsertSoftHyphenTool';
bs.vec.tool.InsertSoftHyphen.static.group = 'object';
bs.vec.tool.InsertSoftHyphen.static.icon = 'softHyphen';
bs.vec.tool.InsertSoftHyphen.static.title = OO.ui.deferMsg( 'bs-vec-insert-softhyphen-title' );

bs.vec.tool.InsertSoftHyphen.static.commandName = 'bsInsertSoftHyphen';
ve.ui.toolFactory.register( bs.vec.tool.InsertSoftHyphen );
