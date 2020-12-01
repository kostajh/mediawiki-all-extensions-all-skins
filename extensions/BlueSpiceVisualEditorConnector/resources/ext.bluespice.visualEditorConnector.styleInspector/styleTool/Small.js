bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.SmallStyleTool = function( config ) {
	bs.vec.ui.SmallStyleTool.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.SmallStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.SmallStyleTool.prototype.getName = function() {
	return "textStyle/small";
};

bs.vec.ui.SmallStyleTool.prototype.getIcon = function() {
	return 'smaller';
};

bs.vec.ui.SmallStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "visualeditor-annotationbutton-small-tooltip" );
};
