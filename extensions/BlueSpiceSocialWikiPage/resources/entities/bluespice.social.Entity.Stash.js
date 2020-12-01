
bs.social.EntityStash = function( $el, type, data ) {
	bs.social.EntityText.call( this, $el, type, data );
	var me = this;
};
OO.inheritClass( bs.social.EntityStash, bs.social.EntityText );

bs.social.EntityStash.prototype.init = function() {
	bs.social.EntityStash.super.prototype.init.apply( this );
	this.startEditor = this.$el.attr( 'data-starteditor' )
		&& this.$el.attr( 'data-starteditor' ).length > 0;
		//Hacky workaround to force restart of the edit mode
	if( !this.editmode && this.startEditor ) {
		this.makeEditMode();
	}
};

bs.social.EntityStash.prototype.makeEditMode = function() {
	bs.social.EntityStash.super.prototype.makeEditMode.apply( this );

	var $attachments = this.getContainer( this.ATTACHMENTS_CONTAINER );

	if( $attachments.length > 0 ) {
		$attachments.show();
	}
};

bs.social.EntityStash.prototype.reset = function( data ) {
	return bs.social.EntityStash.super.prototype.reset.apply( this, [data] );
};

bs.social.EntityStash.prototype.makeEditor = function() {
	return new bs.social.EntityEditorStash(
		this.getEditorConfig(),
		this
	);
};

bs.social.EntityStash.static.name = "\\BlueSpice\\Social\\WikiPage\\Entity\\Stash";
bs.social.factory.register( bs.social.EntityStash );