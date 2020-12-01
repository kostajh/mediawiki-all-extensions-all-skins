Ext.define( 'BS.Bookshelf.menu.TextNode', {
	extend: 'Ext.menu.Menu',
	closeAction:'destroy',

	currentRecord: null,
	treePanel: null,

	constructor: function(cfg) {
		this.treePanel = cfg.treePanel;
		this.currentRecord = cfg.currentRecord;
		this.callParent(arguments);
	},

	initComponent: function() {
		var items = this.makeItems();

		$(document).trigger( 'BSBookshelfContextMenu', [this, items]);

		this.items = [];
		for( var itemkey in items ) {
			this.items.push( items[itemkey] );
		}

		this.callParent(arguments);
	},

	makeItems: function() {
		this.itmEdit = new Ext.menu.Item({
			text: mw.message('bs-bookshelfui-ctxmnu-edit').plain(),
			iconCls: 'icon-pencil',
			handler: this.onItmEditClick,
			scope: this
		});

		this.itmDelete = new Ext.menu.Item({
			text: mw.message('bs-bookshelfui-ctxmnu-delete').plain(),
			iconCls: 'icon-trash',
			handler: this.onItmDeleteClick,
			scope: this
		});

		return {
			'edit' : this.itmEdit,
			'delete': this.itmDelete
		};
	},

	onItmEditClick: function( item, e, eOpts  ) {
		this.treePanel.cellEditing.startEdit( this.currentRecord, 0 );
	},

	onItmDeleteClick: function( item, e, eOpts  ) {
		var node = this.treePanel.getSingleSelection();
		if( node ) {
			node.remove();
		}
	}
});
