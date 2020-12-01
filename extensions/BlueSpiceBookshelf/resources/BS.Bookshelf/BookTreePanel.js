Ext.define( 'BS.Bookshelf.BookTreePanel', {
	extend: 'Ext.tree.Panel',
	requires: [
		'Ext.data.TreeStore',
		'BS.Bookshelf.model.CheckablePageNode',
		'BS.Bookshelf.tree.NumberedNodeColumn'
	],
	hideHeaders: true,
	frame: true,
	//rootVisible: false,
	autoScroll: true,
	useArrows: true,
	height: 550,

	//Custom settings
	useModel: 'BS.Bookshelf.model.CheckablePageNode',
	useColumns: false,
	allowEdit: false,
	treeData: {},
	isDirty: false,

	initComponent: function() {
		this.cellEditing = new Ext.grid.plugin.CellEditing({
			clicksToEdit: 2,
			listeners: {
				beforeedit: function(editor, e, eOpts) {
					if( e.record.isRoot() ) { //Root node is book page and can not be edited by this interface
						return false;
					}
				}
			}
		});
		this.tfCellEditor = new Ext.form.field.Text({
			allowBlank:false
		});

		this.store = new Ext.data.TreeStore({
			root: this.treeData,
			model: 'BS.Bookshelf.model.CheckablePageNode'
		});

		var viewCfg = {};
		if( this.allowEdit ) {
			viewCfg = { //Enable drag & drop
				plugins: {
					ptype: 'treeviewdragdrop',
					containerScroll: true
				}
			};

			this.plugins = [ this.cellEditing ]; //Enable cell editing
			this.on( 'edit', this.onCellEdit, this );
		}

		this.viewConfig = viewCfg;
		this.columns = [/*{
				//this is so we know which column will show the tree
				xtype: 'treecolumn',
				dataIndex: 'text',
				flex: 1,
			}*/
			new BS.Bookshelf.tree.NumberedNodeColumn({
				parentTreePanel: this,
				editor: this.tfCellEditor,
				dataIndex: 'articleDisplayTitle'
			})
		];

		this.on( 'checkchange', this.onCheckChange, this );
		this.on( 'itemappend', this.onItemappend, this );
		this.on( 'iteminsert', this.onIteminsert, this );
		this.on( 'itemremove', this.onItemremove, this );
		this.on( 'itemcontextmenu', this.onItemContextMenu, this );
		//this.on( 'itemdblclick', this.onItemDblClick, this );
		this.callParent( arguments );
	},

	tfCellRenderer: function(value, metaData, record, rowIndex, colIndex, store, view ) {
		var html = value;
		var number = record.get('articleNumber');
		if( number !== '' ) {
			html = number+' ' + html;
		}

		if ( record.get('articleId') === 0 ) {
			html = '<span style="color:red">'+html+'</span>';
		}
		return html;
	},

	//HINT: http://stackoverflow.com/questions/6579769/automatically-check-uncheck-all-subtree-nodes-in-extjs-tree-when-certain-node-ge
	onCheckChange: function( node, checked, options ) {
		node.cascadeBy( function(n){
			n.set('checked', checked);
		});
	},

	onItemappend: function( sender, node, index, eOpts ) {
		this.setDirty();
	},
	onIteminsert: function( sender, node, refNode, eOpts ) {
		this.setDirty();
	},
	onItemremove: function( sender, node, isMove, eOpts ) {
		this.setDirty();
	},
	onItemDblClick: function( sender, record, item, index, e, eOpts ) {
		var linkurl = mw.util.getUrl( record.get('articleTitle') );
		window.open( linkurl );
	},
	onItemContextMenu: function( treepanel, record, item, index, e, eOpts ) {
		if( e.ctrlKey ) {
			return;
		}

		var node = record.get('bookshelf');
		if ( node.nomenu ) {
			return;
		}

		var nodeType = node.type,
			menuClass = 'BS.Bookshelf.menu.TextNode';


		if( nodeType === 'wikipage' ) {
			menuClass = 'BS.Bookshelf.menu.WikiPageNode';
		}
		else if( nodeType === 'tag' ) {
			menuClass = 'BS.Bookshelf.menu.TagNode';
		}
		else if( nodeType !== 'text' ) {
			var o = { menuClass: menuClass }
			$(document).trigger( 'BSBookshelfUIContextMenuClass', [this, o]); // DEPENDENCY
			menuClass = o.menuClass;
		}

		Ext.create( menuClass, {
			currentRecord: record,
			treePanel: this
		}).showAt( e.getXY() );

		e.preventDefault();
		return false;
	},

	onCellEdit: function(editor, e) {
		if( e.originalValue === e.value ){
			return; //If nothing changed no one needs to be notified
		}
		e.record.set( 'articleDisplayTitle', e.value ); //To allow proper rendering by "NumberedNodeColumn"

		this.setDirty();
	},

	getSingleSelection: function() {
		var selectedRecords = this.getSelectionModel().getSelection();
		if( selectedRecords.length > 0) {
			return selectedRecords[0];
		}
		return null;
	},

	setDirty: function() {
		this.isDirty = true;
		this.fireEvent( 'dirty', [this, this.getRootNode()]);
	},

	setClean: function() {
		this.isDirty = false;
	}
});
