bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

bs.ui.widget.TextInputMWVisualEditor = function ( config ) {
	bs.ui.widget.TextInputMWVisualEditor.parent.call( this, config );

	this.selector = config.selector || '.bs-vec-widget';
	this.visualEditor = null;
	this.config = config;
	this.loading = false;
	this.currentValue = config.value || '';
};

OO.inheritClass( bs.ui.widget.TextInputMWVisualEditor, OO.ui.MultilineTextInputWidget );

bs.ui.widget.TextInputMWVisualEditor.prototype.onFocus = function() {
	if( this.loading || this.visualEditor ) {
		return;
	}

	this.makeVisualEditor( this.config );
	$( this.config.selector ).hide();
};

/**
 *
 * @returns {string}
 */
bs.ui.widget.TextInputMWVisualEditor.prototype.getValue = function() {
	return this.currentValue;
};

/**
 *
 * @param {string} value
 * @returns {undefined}
 */
bs.ui.widget.TextInputMWVisualEditor.prototype.setValue = function( value ) {
	if( !this.visualEditor ) {
		this.currentValue = value;
		return;
	}

	this.visualEditor.clearSurfaces();
	this.visualEditor.addSurface(
		ve.dm.converter.getModelFromDom(
			ve.createDocumentFromHtml( value )
		)
	);
};

bs.ui.widget.TextInputMWVisualEditor.prototype.makeVisualEditor = function( config ) {
	var me = this;
	config = config || me.config;

	this.loading = true;
	mw.loader.using( 'ext.bluespice.visualEditorConnector.standalone' ).done( function() {
		me.emit( 'editorStartup', this );
		bs.vec.createEditor( config.id, {
			renderTo: config.selector,
			value: me.currentValue,
			format: config.format
		} ).done( function( target ){
			me.visualEditor = target;
			me.visualEditor.getSurface().getModel().on( 'history', me.onHistoryChange, [], me );
		} ).then( function() {
			me.emit( 'editorStartupComplete', this );
			me.loading = false;
		} );
	} );
};

bs.ui.widget.TextInputMWVisualEditor.prototype.onHistoryChange = function() {
	var me = this;
	this.visualEditor.getWikiText().done( function( value ) {
		me.currentValue = value;
		me.emit( 'change', value );
	} );
};
