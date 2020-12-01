bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.BigStyleTool = function( config ) {
	bs.vec.ui.BigStyleTool.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.BigStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.BigStyleTool.prototype.getName = function() {
	return "textStyle/big";
};

bs.vec.ui.BigStyleTool.prototype.getIcon = function() {
	return 'bigger';
};

bs.vec.ui.BigStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "visualeditor-annotationbutton-big-tooltip" );
};
