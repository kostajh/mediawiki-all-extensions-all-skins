bs = bs || {};
bs.ui = bs.ui || {};
bs.ui.widget = bs.ui.widget || {};

bs.ui.widget.TextInputFileSelect = function ( config ) {
	OO.ui.MultilineTextInputWidget.call( this, config );
	var me = this;
	me.field = config.field || false;
	me.attachments = config.attachments || false;
	me.attachments.on( 'rendercomplete', function( e ) {
		me.render();
	} );
};
OO.initClass( bs.ui.widget.TextInputFileSelect );
OO.inheritClass( bs.ui.widget.TextInputFileSelect, OO.ui.MultilineTextInputWidget );

bs.ui.widget.TextInputFileSelect.prototype.init = function() {
};

bs.ui.widget.TextInputFileSelect.prototype.makeNewAttachmentEditor = function( id ) {
	var addMsg = mw.message(
		'bs-socialwikipage-stash-editor-attachedfile-add'
	);
	var $attachment = $(
		'<div>'
			+ '<span id="' + id + '" class="bs-social-entity-attachment-wrapper editable">'
				+ '<a href="#" class="add" title="' + addMsg.plain() + '">'
				+ '</a>'
			+ '</span>'
		+ '</div>'
	);
	return $attachment;
};

bs.ui.widget.TextInputFileSelect.prototype.render = function() {
	var me = this;
	var id = 'attachmentEditorNew-' + bs.social.generateUniqueId();
	var $newAttachment = me.makeNewAttachmentEditor( id );

	me.attachments.$element.append( $newAttachment );

	$( document ).on( 'click', '#' + id + ' a.add', function( e ) {
		e.preventDefault();
		mw.loader.using( [ 'ext.bluespice.extjs' ] ).done( function () {
			Ext.Loader.setPath(
				'BS.SocialWikiPage',
				bs.em.paths.get( 'BlueSpiceSocialWikiPage' ) + '/resources/BS.SocialWikiPage'
			);
			Ext.require( 'BS.SocialWikiPage.InsertFile.Dialog', function() {
				var diag = new BS.SocialWikiPage.InsertFile.Dialog();
				diag.on( 'ok', function( btn, data ) {
					if( !data.page_title ) {
						return;
					}
					me.emit( 'change', me, {
						files: [ data.page_title ]
					} );
					return;
				});
				diag.show( me );
			});
		} );
		return false;
	} );
};
