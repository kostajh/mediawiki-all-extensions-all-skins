bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.CodeStyleTool = function( config ) {
	bs.vec.ui.CodeStyleTool.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.CodeStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.CodeStyleTool.prototype.getName = function() {
	return "textStyle/code";
};

bs.vec.ui.CodeStyleTool.prototype.getIcon = function() {
	return 'code';
};

bs.vec.ui.CodeStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "visualeditor-annotationbutton-code-tooltip" );
};
