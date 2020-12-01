Ext.define( 'BS.Bookshelf.panel.BookNavigation', {
	extend: 'Ext.panel.Panel',
	requires: [ 'BS.Bookshelf.model.NumberedPageNode',
		'BS.Bookshelf.grid.Books', 'BS.Bookshelf.tree.Book',
		'BS.Bookshelf.toolbar.Pager' ],
	layout: 'fit',
	height: 600,

	currentData: null,

	initComponent: function() {
		this.tools = this.tools || [{
			type:'custom',
			tooltip: mw.message('bs-bookshelf-tag-edit-book').plain(),
			cls: 'bs-bookshelf-edit-book-tool',
			renderTpl: [
				'<span class="icon icon-pencil"></span>'
			],
			handler: function(event, toolEl, panel){

				window.location.href = mw.util.getUrl(
					this.currentData.bsSrc, {
						action: 'edit'
					}
				);
			},
			scope: this
		}];

		var bookTitle = new mw.Title( this.currentData.bsSrc );
		this.setTitle( bookTitle.getMainText() );

		var bookTree = new BS.Bookshelf.tree.Book({
			treeData: this.currentData.bsTree,
			bookSrc: this.currentData.bsSrc,
			currentArticleId: mw.config.get('wgArticleId'),
			autoTitle: false
		});
		var bookPager = new BS.Bookshelf.toolbar.Pager({
			bookTreePanel: bookTree
		});

		this.items = [
			bookTree
		];
		this.dockedItems = [
			bookPager
		];

		this.callParent(arguments);
	}
});
