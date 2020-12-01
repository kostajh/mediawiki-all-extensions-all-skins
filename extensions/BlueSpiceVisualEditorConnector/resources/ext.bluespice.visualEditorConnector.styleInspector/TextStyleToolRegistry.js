bs.util.registerNamespace( 'bs.vec.registry' );

bs.vec.TextStyleToolRegistry = function() {
	OO.Registry.call( this );
};

OO.inheritClass( bs.vec.TextStyleToolRegistry, OO.Registry );

bs.vec.registry.TextStyleTool = new bs.vec.TextStyleToolRegistry();

bs.vec.registry.TextStyleTool.register( "textStyle/bold", { constructor: bs.vec.ui.BoldStyleTool } );
bs.vec.registry.TextStyleTool.register( "textStyle/italic", { constructor: bs.vec.ui.ItalicStyleTool } );
bs.vec.registry.TextStyleTool.register( "textStyle/color", { constructor: bs.vec.ui.ColorStyleTool } );
bs.vec.registry.TextStyleTool.register( "textStyle/strikethrough", { constructor: bs.vec.ui.StrikethroughStyleTool } );
bs.vec.registry.TextStyleTool.register( "textStyle/underline", { constructor: bs.vec.ui.UnderlineStyleTool } );
bs.vec.registry.TextStyleTool.register( "textStyle/small", { constructor: bs.vec.ui.SmallStyleTool } );
bs.vec.registry.TextStyleTool.register( "textStyle/big", { constructor: bs.vec.ui.BigStyleTool } );
bs.vec.registry.TextStyleTool.register( "textStyle/code", { constructor: bs.vec.ui.CodeStyleTool } );