Ext.define( 'BS.Bookshelf.menu.TagNode', {
	extend: 'BS.Bookshelf.menu.TextNode',

	makeItems: function() {
		this.itmTagEdit = new Ext.menu.Item({
			text: mw.message('bs-bookshelfui-ctxmnu-tag-edit').plain(),
			iconCls: 'icon-wrench',
			handler: this.onItmTagEditClick,
			scope: this
		});

		var items = this.callParent(arguments);
		items.tagedit = this.itmTagEdit;

		return items;
	},

	onItmTagEditClick: function( item, e, eOpts  ) {
		Ext.create('BS.Bookshelf.dialog.TagSettings', {
			currentRecord: this.currentRecord
		}).show();
	}
});
