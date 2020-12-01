bs.util.registerNamespace( 'bs.vec.ui.plugin' );

bs.vec.ui.plugin.MWMetaDialog = function BsVecUiPluginMWMetaDialog( component ) {
	this.component = component;
};

OO.initClass( bs.vec.ui.plugin.MWMetaDialog );

bs.vec.ui.plugin.MWMetaDialog.prototype.initialize = function() {
	// do nothing
};

bs.vec.ui.plugin.MWMetaDialog.prototype.getSetupProcess = function( parentProcess, data ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWMetaDialog.prototype.getActionProcess = function( parentProcess, action ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWMetaDialog.prototype.getTeardownProcess = function( parentProcess, data ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWMetaDialog.prototype.swapPanel = function ( panel, noFocus ) {
	//do nothing
};