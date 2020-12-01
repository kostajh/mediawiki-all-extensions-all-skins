Ext.define( 'BS.Bookshelf.model.CheckablePageNode', {
	extend: 'Ext.data.Model',
	fields: [
		//TODO: This is code duplication of BS.Bookshelf.model.NumberedPageNode
		//Custom data (legacy)
		{ name: 'articleNumber', type: 'string' },
		{ name: 'articleTitle', type: 'string' },
		{ name: 'articleDisplayTitle', type: 'string' },
		{ name: 'articleId', type: 'int' },
		{ name: 'articleIsRedirect', type: 'boolean' },

		{ name: 'bookshelf', type: 'auto', defaultValue: {} },

		//NodeInterface data
		{ name: 'text', type: 'string' },

		//Modifing the icon
		{ name: 'iconCls', type: 'string', convert: function( val, record ) {
			return 'icon-'+record.data.bookshelf.type;
		}},

		//HINT: http://www.sencha.com/forum/showthread.php?12915-1.1-Drag-drop-grid-tree-append-leaf
		{ name: 'leaf', type: 'boolean', defaultValue: false },
		{ name: 'expanded', type: 'boolean', defaultValue: true },

		//Show chekboxes
		{ name: 'checked', type: 'boolean', defaultValue: false }
	]
});
