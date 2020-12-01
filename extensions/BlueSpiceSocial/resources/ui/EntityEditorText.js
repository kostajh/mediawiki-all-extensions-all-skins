bs.social.EntityEditorText = function ( config, entity ) {
	if ( typeof config.visualEditor === 'undefined' ) {
		this.visualEditor =  true;
	}
	if( this.visualEditor && !mw.config.get( 'bsgVisualEditorConnectorEnableVisualEditor', false ) ) {
		this.visualEditor = false;
	}
	bs.social.EntityEditor.call( this, config, entity );
};

OO.initClass( bs.social.EntityEditorText );
OO.inheritClass( bs.social.EntityEditorText, bs.social.EntityEditor );

bs.social.EntityEditorText.prototype.makeFields = function() {
	var fields = bs.social.EntityEditorText.super.prototype.makeFields.apply(
		this
	);
	var cfg = {
		placeholder: this.getEntity().data.get( 'text', '' ) || this.getVarLabel( 'text' ),
		autosize: true,
		value: this.getEntity().data.get( 'text', '' )
	};

	if( this.visualEditor ) {
		var entityUiID = this.getEntity().makeUiID();
		cfg.id = entityUiID + '-ve';
		cfg.classes = ['bs-social-visualeditor-text'];
		cfg.selector = '#' + entityUiID + ' .bs-social-visualeditor-text textarea:first';
		this.text = new bs.ui.widget.TextInputMWVisualEditor( cfg );
		this.text.on( 'editorStartup', this.onEditorStartup, [], this );
		this.text.on( 'editorStartupComplete', this.onEditorStartupComplete, [], this );
	} else {
		this.text = new OO.ui.MultilineTextInputWidget( cfg );
	}
	fields.text = this.text;
	return fields;
};
bs.social.EntityEditorText.prototype.addContentFieldsetItems = function() {
	this.contentfieldset.addItems( [
		new OO.ui.FieldLayout( this.text, {
			label: this.getVarLabel( 'text' ),
			align: 'top'
		} )
	]);
	bs.social.EntityEditorText.super.prototype.addContentFieldsetItems.apply(
		this
	);
};

bs.social.EntityEditorText.prototype.getShortModeField = function() {
	return this.text;
};
