Ext.define( 'BS.Bookshelf.BookNavigation',{
	extend: 'Ext.Panel',
	requires: [
		'BS.Bookshelf.tree.Book', 'BS.Bookshelf.toolbar.Pager'
	],
	//hideBorders: true,
	frame: true,
	layout: 'fit',
	height: '100%',

	//Custom fields
	oCurrentArticleNode: null,
	treeData: {},
	bookSrc: '',

	initComponent: function() {

		this.tpChapters = new BS.Bookshelf.tree.Book({
			treeData: this.treeData,
			currentArticleId: mw.config.get('wgArticleId'),
			tools : this.tools || [{
				type:'gear',
				tooltip: mw.message('bs-bookshelf-tag-edit-book').plain(),
				cls: 'bs-bookshelf-edit-book-tool',
				handler: function(event, toolEl, panel){
					window.location.href = mw.util.getUrl(
						this.bookSrc, {
							action: 'edit'
						}
					);
				},
				scope: this
			}]
		});

		this.tools = []; //We remove the tools from the parent panel to avoid strange layout

		this.items = [
			this.tpChapters
		];

		this.tbPager = new BS.Bookshelf.toolbar.Pager({
			bookTreePanel: this.tpChapters,
			dock: 'bottom',
			ui: 'footer'
		});

		this.dockedItems = [
			this.tbPager
		];

		this.callParent(arguments);
	}
});
