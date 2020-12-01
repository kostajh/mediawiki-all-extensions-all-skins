Ext.define( 'BS.Bookshelf.MetaDialog', {
	extend: 'MWExt.Dialog',
	requires: [ 'BS.Bookshelf.MetaGrid' ],

	width: 600,
	autoHeight: true,
	modal:true,

	//Custom settings
	metaData: [],
	metaDataConfig: [],

	makeItems: function() {
		this.setTitle( mw.message('bs-bookshelfui-dlg-metadata-title').plain() );
		this.mgMeta = new BS.Bookshelf.MetaGrid({
			metaData: this.metaData,
			metaDataConfig: this.metaDataConfig
		});
		return [
			this.mgMeta
		];
	},

	getData: function() {
		var metas = this.mgMeta.getData();
		return this.mgMeta.getData(metas);
	},

	makeMainFormPanel: function() {
		var mainFormPanel = this.callParent();
		mainFormPanel.getForm().clearListeners();
		return mainFormPanel;
	}
});
