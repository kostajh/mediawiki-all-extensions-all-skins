bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.ColorAnnotationTool = function() {
	ve.ui.BoldAnnotationTool.super.apply( this, arguments );
};
OO.inheritClass( bs.vec.ui.ColorAnnotationTool, ve.ui.AnnotationTool );
bs.vec.ui.ColorAnnotationTool.static.name = 'color';
bs.vec.ui.ColorAnnotationTool.static.group = 'textStyle';
bs.vec.ui.ColorAnnotationTool.static.icon = 'textColor';
bs.vec.ui.ColorAnnotationTool.static.title =
	OO.ui.deferMsg( "bs-visualeditorconnector-text-style-tool-color-label" );
bs.vec.ui.ColorAnnotationTool.static.annotation = { name: 'textStyle/color' };
bs.vec.ui.ColorAnnotationTool.static.commandName = 'color';
ve.ui.toolFactory.register( bs.vec.ui.ColorAnnotationTool );