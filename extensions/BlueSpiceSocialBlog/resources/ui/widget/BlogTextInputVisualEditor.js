bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

bs.ui.widget.BlogTextInputVisualEditor = function ( config ) {
	bs.ui.widget.TextInputVisualEditor.call( this, config );
};
OO.initClass( bs.ui.widget.BlogTextInputVisualEditor );
OO.inheritClass( bs.ui.widget.BlogTextInputVisualEditor, bs.ui.widget.TextInputVisualEditor );

bs.ui.widget.BlogTextInputVisualEditor.prototype.getPluginNames = function() {
	var plugins = bs.ui.widget.BlogTextInputVisualEditor.super.prototype.getPluginNames.apply( this );
	plugins.push( 'textcolor' );
	plugins.push( 'colorpicker' );
	plugins.push( 'table' );
	plugins.push( 'fullscreen' );
	return plugins;
};