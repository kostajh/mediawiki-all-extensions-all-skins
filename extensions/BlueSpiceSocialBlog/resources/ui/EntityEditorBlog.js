bs.social.EntityEditorBlog = function ( config, entity ) {
	bs.social.EntityEditorText.call( this, config, entity );
};
OO.initClass( bs.social.EntityEditorBlog );
OO.inheritClass( bs.social.EntityEditorBlog, bs.social.EntityEditorText );

bs.social.EntityEditorBlog.prototype.makeFields = function() {
	var fields = bs.social.EntityEditorBlog.super.prototype.makeFields.apply(
		this
	);

	// Add extra config to the editor. This will probably be used again
	// when the switch to MediaWiki VisualEditor is finished.
	// Please leave this code for future reference
//	if( this.visualEditor ) {
//		$(document).trigger('BSSocialEntityEditorBlogEditorConfig', [
//			this,
//			cfg
//		]);
//	}

	this.blogtitle = new OO.ui.TextInputWidget( {
		placeholder: this.getVarLabel( 'blogtitle' ),
		autosize: false,
		value: this.getEntity().data.get( 'blogtitle', '' )
	});
	fields.blogtitle = this.blogtitle;

	this.teasertext = new OO.ui.MultilineTextInputWidget( {
		placeholder: this.getVarLabel( 'teasertext' ),
		autosize: true,
		value: this.getEntity().data.get( 'teasertext', '' ),
		rows: 5
	});
	fields.teasertext = this.teasertext;

	return fields;
};

bs.social.EntityEditorBlog.prototype.addContentFieldsetItems = function() {
	this.contentfieldset.addItems( [
		new OO.ui.FieldLayout( this.blogtitle, {
			label: this.getVarLabel( 'blogtitle' ),
			align: 'top'
		})
	]);
	bs.social.EntityEditorBlog.super.prototype.addContentFieldsetItems.apply(
		this
	);
};

bs.social.EntityEditorBlog.prototype.addAdvancedFieldsetItems = function() {
	if( !mw.config.get( 'bsBSSocialUseBlogTeaser', true ) ) {
		return bs.social.EntityEditorBlog.super.prototype.addAdvancedFieldsetItems.apply(
			this
		);
	}
	this.advancedfieldset.addItems( [
		new OO.ui.FieldLayout( this.teasertext, {
			label: this.getVarLabel( 'teasertext' ),
			align: 'top'
		})
	]);
	bs.social.EntityEditorBlog.super.prototype.addAdvancedFieldsetItems.apply(
		this
	);
};

bs.social.EntityEditorBlog.prototype.getShortModeField = function() {
	return this.blogtitle;
};
