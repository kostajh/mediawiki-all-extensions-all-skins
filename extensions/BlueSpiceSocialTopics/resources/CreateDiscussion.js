bs.social.CreateDiscussion = function( $el ) {

	bs.social.El.call( this, $el );
	var me = this;
	me.BUTTON_SECTION = 'bs-socialtopics-discussion-create';
	me.data = {};
	me.makeUiID();

	me.$button = me.getEl().find( '.' + me.BUTTON_SECTION + ' a' ).first();
	if( !me.$button || me.$button.length < 1 ) {
		return;
	}

	me.$button.on( 'click', function( e ) {
		me.createDiscussion( $( this ).parent().attr('value') );
		e.stopPropagation();
		return false;
	});
	$(document).trigger( 'BSSocialCreateDiscussionInit', [ me, $el ] );

};
OO.initClass( bs.social.CreateDiscussion );
OO.inheritClass( bs.social.CreateDiscussion, bs.social.El );

bs.social.CreateDiscussion.prototype.createDiscussion = function( id ) {
	var dfd = $.Deferred();
	var taskData = {
		'discussiontitleid': id,
		'type': 'discussion'
	};

	this.showLoadMask();
	var me = this;
	bs.api.tasks.execSilent( 'social', 'editEntity', taskData )
	.done( function( response ) {
		//ignore errors for now
		//me.replaceEL( response.payload.view );
		if( !response.success ) {
			if( response.message && response.message !== '' ) {
				OO.ui.alert( response.message );
			}
			me.hideLoadMask();
			dfd.resolve( me );
			return;
		}
		me.loadDiscussionPage( response );
		dfd.resolve( me );
	});

	return dfd;
};

bs.social.CreateDiscussion.prototype.reloadPage = function() {
	window.location = mw.util.getUrl(
		mw.config.get( 'wgPageName' )
	);
};

bs.social.CreateDiscussion.prototype.loadDiscussionPage = function( data ) {
	if ( !data || !data.payload || !data.payload.entity ) {
		this.reloadPage();
	}
	var entity = JSON.parse( data.payload.entity );
	if ( !entity || !entity.relatedtitle ) {
		this.reloadPage();
	}

	window.location = mw.util.getUrl( entity.relatedtitle );
};
