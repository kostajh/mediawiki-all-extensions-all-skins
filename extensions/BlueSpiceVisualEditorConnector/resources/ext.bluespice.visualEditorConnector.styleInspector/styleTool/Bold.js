bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.BoldStyleTool = function( config ) {
	bs.vec.ui.BoldStyleTool.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.BoldStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.BoldStyleTool.prototype.getName = function() {
	return "textStyle/bold";
};

bs.vec.ui.BoldStyleTool.prototype.getIcon = function() {
	return 'bold';
};

bs.vec.ui.BoldStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "visualeditor-annotationbutton-bold-tooltip" );
};
