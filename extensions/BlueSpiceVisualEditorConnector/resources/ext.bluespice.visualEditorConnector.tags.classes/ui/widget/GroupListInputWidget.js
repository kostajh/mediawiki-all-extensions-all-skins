bs.util.registerNamespace( 'bs.vec.ui' );

bs.vec.ui.GroupListInputWidget = function BsVecUiGroupListInputWidget ( config ) {
	bs.vec.ui.GroupListInputWidget.super.call( this, config );
	this.inspector = config.inspector;
	this.attribute = config.attribute;
	this.setDisabled( true );
	me = this;
	this.getGroups().done( function( options ) {
		me.addOptions( options );
		me.setDisabled( false );
		me.setValue( me.inspector.selectedNode.getAttribute( 'mw' ).attrs[me.attribute.name] || me.attribute.default );
	});
};

OO.inheritClass( bs.vec.ui.GroupListInputWidget, OO.ui.MenuTagMultiselectWidget );

bs.vec.ui.GroupListInputWidget.prototype.getValue = function() {
	var value = bs.vec.ui.GroupListInputWidget.super.prototype.getValue.call( this );
	return value.join( "," );
}

bs.vec.ui.GroupListInputWidget.prototype.setValue = function( value ) {
	// remove any whitespace around commas
	var value = value.replace( /[\s,]+/g, ',' );
	value = value.split( "," );
	return bs.vec.ui.GroupListInputWidget.super.prototype.setValue.call( this, value );
}

bs.vec.ui.GroupListInputWidget.prototype.getGroups = function() {
	var dfd = $.Deferred();
	bs.api.store.getData( 'group' ).done( function( response ) {
		var results = response.results;
		var options = [];
		for ( var i = 0; i < results.length; i++ ) {
			options.push({
				data: results[i].group_name,
				label: results[i].displayname
			});
		};
		dfd.resolve( options );
	});
	return dfd.promise();
}
