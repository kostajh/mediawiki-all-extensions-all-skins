bs.social.CreateFirstAttachmentAfterContent = function( $el ) {

	bs.social.El.call( this, $el );
	var me = this;
	me.BUTTON_SECTION = 'bs-socialwikipage-attachment-create-first';
	me.data = {};
	me.makeUiID();

	me.$button = me.getEl().find( '.' + me.BUTTON_SECTION + ' a' ).first();
	if( !me.$button || me.$button.length < 1 ) {
		return;
	}
	me.$button.on( 'click', function( e ) {
		me.goToSpecialAttachments();
		e.stopPropagation();
		return false;
	});
	$( document ).trigger( 'BSSocialCreateFirstAttachmentAfterContentInit', [ me, $el ] );

};
OO.initClass( bs.social.CreateFirstAttachmentAfterContent );
OO.inheritClass( bs.social.CreateFirstAttachmentAfterContent, bs.social.El );

bs.social.CreateFirstAttachmentAfterContent.prototype.reloadPage = function() {
	window.location = mw.util.getUrl(
		mw.config.get( 'wgPageName' )
	);
};

bs.social.CreateFirstAttachmentAfterContent.prototype.goToSpecialAttachments = function() {
	window.location = mw.util.getUrl(
		'Special:Attachments/' + mw.config.get( 'wgPageName' )
	);
};
