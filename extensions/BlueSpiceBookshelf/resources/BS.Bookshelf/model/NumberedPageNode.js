Ext.define( 'BS.Bookshelf.model.NumberedPageNode', {
	extend: 'Ext.data.Model',
	fields: [
		//Custom data (legacy)
		{ name: 'articleNumber', type: 'string' },
		{ name: 'articleTitle', type: 'string' },
		{ name: 'articleDisplayTitle', type: 'string' },
		{ name: 'articleId', type: 'int' },
		{ name: 'articleIsRedirect', type: 'boolean' },

		{ name: 'bookshelf', type: 'auto', defaultValue: {} },

		//NodeInterface data
		{ name: 'text', type: 'string' }, //TODO: Remove this from the source data and calculate from 'articleNumber' and 'articleDisplayTitle'
		{ name: 'qtip', type: 'string', mapping: 'text' },

		//Hidding the icon
		{ name: 'iconCls', type: 'string', defaultValue: 'bs-bookshelf-no-icon'},

		//Force rendering as anchor tag
		{ name: 'href', type: 'string', convert: function( val, record ) {
			if( record.data.bookshelf.type !== 'wikipage' ) {
				return '#';
			}

			//Make "real" links! So a user can "open in new window"
			if( record.data && record.data.articleTitle ) {
				return mw.util.getUrl( record.data.articleTitle );
			}
			return val;
		}},

		//HINT: http://www.sencha.com/forum/showthread.php?12915-1.1-Drag-drop-grid-tree-append-leaf
		//{ name: 'leaf', type: 'boolean', defaultValue: true },
		{ name: 'expanded', type: 'boolean', defaultValue: true }
	]
});