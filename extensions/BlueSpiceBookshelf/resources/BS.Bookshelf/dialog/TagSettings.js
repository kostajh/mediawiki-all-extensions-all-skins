Ext.define('BS.Bookshelf.dialog.TagSettings', {
	extend: 'MWExt.Dialog',
	title: mw.message('bs-bookshelfui-dlg-tagsettings-title').plain(),

	makeItems: function() {
		this.pgProperties = new Ext.grid.PropertyGrid({
			source: this.currentRecord.get('bookshelf').arguments
		});
		return [
			this.pgProperties
		];
	}
});
