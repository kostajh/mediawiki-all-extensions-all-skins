bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWTableDialog = function BsVecUiMWTableDialog( config ) {
	bs.vec.ui.MWTableDialog.super.call( this, config );
};

OO.inheritClass( bs.vec.ui.MWTableDialog, ve.ui.MWTableDialog );

bs.vec.ui.MWTableDialog.prototype.initialize = function() {
	bs.vec.ui.MWTableDialog.super.prototype.initialize.call( this );

	this.initComponentPlugins();

	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		plugin.initialize();
	}
};

bs.vec.ui.MWTableDialog.prototype.getValues = function () {
	var values = bs.vec.ui.MWTableDialog.super.prototype.getValues.call( this );

	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		values = plugin.getValues( values );
	}
	return values;
};


bs.vec.ui.MWTableDialog.prototype.getSetupProcess = function ( data ) {
	var parentProcess = bs.vec.ui.MWTableDialog.super.prototype.getSetupProcess.call( this, data );
	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		parentProcess = plugin.getSetupProcess( parentProcess, data );
	}
	return parentProcess;
};

bs.vec.ui.MWTableDialog.prototype.getActionProcess = function ( action ) {
	var parentProcess = bs.vec.ui.MWTableDialog.super.prototype.getActionProcess.call( this, action );
	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		plugin.getActionProcess( parentProcess, action );
	}
	return parentProcess;
};

bs.vec.ui.MWTableDialog.prototype.initComponentPlugins = function() {
	this.componentPlugins = [];

	var pluginCallbacks = bs.vec.getComponentPlugins(
		bs.vec.components.TABLE_DIALOG
	);

	for( var i = 0; i < pluginCallbacks.length; i++ ) {
		var callback = pluginCallbacks[i];
		this.componentPlugins.push( callback( this ) );
	}
};