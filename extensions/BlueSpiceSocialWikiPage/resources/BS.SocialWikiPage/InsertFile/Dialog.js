Ext.define( 'BS.SocialWikiPage.InsertFile.Dialog', {
	extend: 'MWExt.Dialog',
	requires: [
		'BS.SocialWikiPage.InsertFile.grid.FileRepo'
	],
	title: mw.message( 'bs-socialwikipage-stash-insertfiledialog-title' ).plain(),

	makeButtons: function() {
		var buttons = this.callParent();
		this.btnOK.setText( mw.message( 'bs-socialwikipage-stash-insertfiledialog-btn-save-label' ).plain() );
		this.btnCancel.setText( mw.message( 'bs-extjs-cancel' ).plain() );
		return buttons;
	},

	makeMainFormPanel: function() {
		this.mainFormPanel = new BS.SocialWikiPage.InsertFile.grid.FileRepo({
			layout: 'anchor'
		});
		return this.mainFormPanel;
	},

	getData: function(){
		return this.mainFormPanel.getData();
	}
});
