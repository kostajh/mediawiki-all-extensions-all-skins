
bs.social.EntityList.CreateDiscussionPage = function( $el, entityList ) {

	bs.social.El.call( this, $el );
	var me = this;
	me.BUTTON_SECTION = 'bs-socialtopics-discussionpage-create';
	me.entityList = entityList;
	me.data = {};
	me.makeUiID();

	me.$button = me.getEl().find( '.' + me.BUTTON_SECTION + ' a' ).first();
	if( !me.$button || me.$button.length < 1 ) {
		return;
	}
	var skip = mw.user.options.get( 'bs-social-topics-skipcreatedialog', false );
	me.$button.on( 'click', function( e ) {
		skip === false ? me.showCreateDiscussionPage() : me.createDiscussionPage();
		e.stopPropagation();
		return false;
	});
	$(document).trigger( 'BSSocialEntityListCreateDiscussionPageInit', [ me, $el ] );

};
OO.initClass( bs.social.EntityList.CreateDiscussionPage );
OO.inheritClass( bs.social.EntityList.CreateDiscussionPage, bs.social.El );

bs.social.EntityList.CreateDiscussionPage.prototype.showCreateDiscussionPage = function() {
	var factory = new OO.Factory();
	var windowManager = new OO.ui.WindowManager( {
		factory: factory
	} );
	factory.register( bs.social.EntityList.CreateDiscussionPage.Dialog );
	$( 'body' ).append( windowManager.$element );

	windowManager.openWindow( 'createDiscussion', {
		text: this.getText(),
		title: this.getTitle(),
		entityList: this.entityList,
		save: this.save
	} );
	return true;
};

bs.social.EntityList.CreateDiscussionPage.prototype.createDiscussionPage = function() {
	return this.save( this.getTitle(), this.getText() );
};

bs.social.EntityList.CreateDiscussionPage.prototype.getText = function() {
	var nsFile = mw.config.get( 'wgNamespaceIds' )['file'];
	var msg = mw.config.get( 'wgNamespaceNumber', 0 ) === nsFile
		? "bs-socialtopics-autocreated-discussionpagefile"
		: "bs-socialtopics-autocreated-discussionpage";
	return mw.message( msg ).plain();
};

bs.social.EntityList.CreateDiscussionPage.prototype.getTitle = function() {
	var nsText = bs.util.getNamespaceText(
		mw.config.get( 'wgNamespaceNumber', 0 ) +1
	);
	return nsText + ':' + mw.config.get( 'wgTitle' );
};

bs.social.EntityList.CreateDiscussionPage.prototype.save = function( title, text ) {
	this.entityList.showLoadMask();
	var api = new mw.Api();
	var me = this;
	return api.postWithToken( 'csrf', {
		action: 'edit',
		format: 'json',
		title: title,
		text: text
	}).done( function( data ) {
		me.loadDiscussionPage( data );
	});
};

bs.social.EntityList.CreateDiscussionPage.prototype.loadDiscussionPage = function( data ) {
	window.location = mw.util.getUrl( this.getTitle() );
};


