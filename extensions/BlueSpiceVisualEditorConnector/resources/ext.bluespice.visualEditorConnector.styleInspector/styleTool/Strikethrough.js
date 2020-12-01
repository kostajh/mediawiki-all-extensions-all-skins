bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.StrikethroughStyleTool = function( config ) {
	bs.vec.ui.StrikethroughStyleTool.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.StrikethroughStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.StrikethroughStyleTool.prototype.getName = function() {
	return "textStyle/strikethrough";
};

bs.vec.ui.StrikethroughStyleTool.prototype.getIcon = function() {
	return 'strikethrough';
};

bs.vec.ui.StrikethroughStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "visualeditor-annotationbutton-strikethrough-tooltip" );
};
