bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.MWMetaDialog = function BsVecUiMWMetaDialog ( config ) {
	bs.vec.ui.MWMetaDialog.super.call( this, ve.extendObject( { padded: false }, config ) );
};

OO.inheritClass( bs.vec.ui.MWMetaDialog, ve.ui.MWMetaDialog );

bs.vec.ui.MWMetaDialog.prototype.initialize = function () {
	this.initComponentPlugins();
	bs.vec.ui.MWMetaDialog.super.prototype.initialize.call( this );

	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		plugin.initialize();
	}
	this.emit( 'initComplete' );
};

bs.vec.ui.MWMetaDialog.prototype.initComponentPlugins = function() {
	this.componentPlugins = [];

	var pluginCallbacks = bs.vec.getComponentPlugins(
		bs.vec.components.META_DIALOG
	);

	for( var i = 0; i < pluginCallbacks.length; i++ ) {
		var callback = pluginCallbacks[i];
		this.componentPlugins.push( callback( this ) );
	}
};

bs.vec.ui.MWMetaDialog.prototype.getSetupProcess = function ( data ) {
	var parentProcess = bs.vec.ui.MWMetaDialog.super.prototype.getSetupProcess.call( this, data );
	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		parentProcess = plugin.getSetupProcess( parentProcess, data );
	}
	return parentProcess;
};

bs.vec.ui.MWMetaDialog.prototype.getTeardownProcess = function ( data ) {
	var parentProcess = bs.vec.ui.MWMetaDialog.super.prototype.getTeardownProcess.call( this, data );

	for( var i = 0; i < this.componentPlugins.length; i++ ) {
		var plugin = this.componentPlugins[i];
		parentProcess = plugin.getTeardownProcess( parentProcess, data );
	}

	return parentProcess;

};