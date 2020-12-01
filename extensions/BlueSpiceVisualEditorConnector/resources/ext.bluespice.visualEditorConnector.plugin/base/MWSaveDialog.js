bs.util.registerNamespace( 'bs.vec.ui.plugin' );

bs.vec.ui.plugin.MWSaveDialog = function BsVecUiPluginMWSaveDialog( component ) {
	this.component = component;
};

OO.initClass( bs.vec.ui.plugin.MWSaveDialog );

bs.vec.ui.plugin.MWSaveDialog.prototype.initialize = function() {
	// do nothing
};

bs.vec.ui.plugin.MWSaveDialog.prototype.getSetupProcess = function( parentProcess, data ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWSaveDialog.prototype.getActionProcess = function( parentProcess, action ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWSaveDialog.prototype.swapPanel = function ( panel, noFocus ) {
	//do nothing
}