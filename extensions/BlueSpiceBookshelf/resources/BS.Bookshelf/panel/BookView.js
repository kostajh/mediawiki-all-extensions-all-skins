Ext.define('BS.Bookshelf.panel.BookView', {
	extend: 'Ext.Panel',
	requires: [
		'BS.Bookshelf.model.CheckablePageNode',
		'BS.Bookshelf.BookTreePanel', 'BS.Bookshelf.TreeHelper',
		'BS.Bookshelf.dialog.WikiPageNode'
	],
	width: 'auto',

	bookTree: {},
	bookType: '',

	initComponent: function() {

		this.tpChapters = new BS.Bookshelf.BookTreePanel( {
			allowEdit: false,
			treeData: this.bookTree
		} );

		this.bookExport = new bs.bookshelf.BookTreeExport( this.tpChapters, {
			onlyChecked: true,
			bookType: this.bookType,
			id: this.getId(),
			setLoadCallback: function( loading ) {
				this.setLoading( loading );
			}.bind( this )
		} );

		this.items = this.makeItems();
		if ( this.bookExport.getExportButton() ) {
			this.tbar = new Ext.Toolbar({
				items: [
					'->',
					this.bookExport.getExportButton()
				]
			} );
		}

		this.callParent(arguments);
	},

	makeItems: function() {
		return [
			this.tpChapters
		];
	}
});
