bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.UnderlineStyleTool = function( config ) {
	bs.vec.ui.UnderlineStyleTool.parent.call( this, config );
};

OO.inheritClass( bs.vec.ui.UnderlineStyleTool, bs.vec.ui.ButtonStyleTool );

bs.vec.ui.UnderlineStyleTool.prototype.getName = function() {
	return "textStyle/underline";
};

bs.vec.ui.UnderlineStyleTool.prototype.getIcon = function() {
	return 'underline';
};

bs.vec.ui.UnderlineStyleTool.prototype.getLabel = function() {
	return OO.ui.deferMsg( "visualeditor-annotationbutton-underline-tooltip" );
};
