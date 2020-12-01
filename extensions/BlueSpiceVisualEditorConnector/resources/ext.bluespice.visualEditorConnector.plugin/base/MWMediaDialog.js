bs.util.registerNamespace( 'bs.vec.ui.plugin' );

bs.vec.ui.plugin.MWMediaDialog = function BsVecUiPluginMWMediaDialog( component ) {
	this.component = component;
};

OO.initClass( bs.vec.ui.plugin.MWMediaDialog );

bs.vec.ui.plugin.MWMediaDialog.prototype.initialize = function() {
	// do nothing
};

bs.vec.ui.plugin.MWMediaDialog.prototype.getSetupProcess = function( parentProcess, data ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWMediaDialog.prototype.getActionProcess = function( parentProcess, action ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWMediaDialog.prototype.setNewUploadBooklet = function() {
	// do nothing
};