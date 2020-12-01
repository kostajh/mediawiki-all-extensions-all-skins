bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ItalicStyleTool = function( config ) {
	bs.vec.ui.ItalicStyleTool.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.ItalicStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.ItalicStyleTool.prototype.getName = function() {
	return "textStyle/italic";
};

bs.vec.ui.ItalicStyleTool.prototype.getIcon = function() {
	return 'italic';
};

bs.vec.ui.ItalicStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "visualeditor-annotationbutton-italic-tooltip" );
};
