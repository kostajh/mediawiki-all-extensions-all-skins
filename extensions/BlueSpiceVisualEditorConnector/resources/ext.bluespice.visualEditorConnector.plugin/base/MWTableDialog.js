bs.util.registerNamespace( 'bs.vec.ui.plugin' );

bs.vec.ui.plugin.MWTableDialog = function BsVecUiPluginMWTableDialog( component ) {
	this.component = component;
};

OO.initClass( bs.vec.ui.plugin.MWTableDialog );

bs.vec.ui.plugin.MWTableDialog.prototype.initialize = function() {
	// do nothing
};

bs.vec.ui.plugin.MWTableDialog.prototype.getValues = function( values ) {
	return values;
};

bs.vec.ui.plugin.MWTableDialog.prototype.getSetupProcess = function( parentProcess, data ) {
	return parentProcess;
};

bs.vec.ui.plugin.MWTableDialog.prototype.getActionProcess = function( parentProcess, action ) {
	return parentProcess;
};