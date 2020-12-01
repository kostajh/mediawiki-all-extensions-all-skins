Ext.define( 'BS.Bookshelf.MetaGrid', {
	extend: 'Ext.grid.property.Grid',
	requires: [ 'Ext.Button', 'Ext.data.JsonStore',
		'Ext.grid.plugin.CellEditing', 'Ext.form.field.Text',
		'BS.Bookshelf.MetaKeyComboBox'],

	stripeRows: true,
	hideHeaders: true,
	height: 200,
	clicksToEdit: 2,

	//Custom settings
	metaData: [],
	metaDataConfig: {},

	initComponent: function() {
		this.btnAdd = new Ext.Button({
			text: mw.message('bs-extjs-add').plain(),
			iconCls: 'icon-fff-add'
		});
		this.btnAdd.on( 'click', this.onBtnAddClick, this );

		this.btnRemove = new Ext.Button({
			text: mw.message('bs-extjs-remove').plain(),
			iconCls: 'icon-fff-delete'
		});
		this.btnRemove.on( 'click', this.onBtnRemoveClick, this );

		//TODO: Add "BS.form.SimpleSelectBox" to BSF and use it
		this.cbMetaKeys = new BS.Bookshelf.MetaKeyComboBox({
			bsData: this.metaDataConfig,
			width: '280px'
		});

		this.dockedItems = [{
			xtype: 'toolbar',
			dock: 'top',
			items: [
				this.cbMetaKeys,
				this.btnAdd,
				this.btnRemove
			]
		}];

		$(document).trigger('BSBookshelfMetaGridInit', [this, this.metaData, this.metaDataConfig]);

		this.source = this.metaData;
		this.sourceConfig = this.metaDataConfig;

		//This is a ugly hack because ExtJS PropertyGrid has no config for
		//column width
		this.on( 'render', function(grid) {
			grid.columns[0].width = 220;
		}, this);

		this.callParent(arguments);
	},

	//HINT:http://dev.sencha.com/deploy/ext-4.0.0/examples/grid/row-editing.js
	onBtnAddClick: function(sender, e, eOpts) {
		var key = this.cbMetaKeys.getValue();
		if( key === '' || key === null ) {
			return;
		}

		var currentSource = this.getSource();
		currentSource[key] = '';
		this.setSource(currentSource);
		this.cbMetaKeys.reset();
	},

	onBtnRemoveClick: function(sender, e, eOpts) {
		var selection = this.getSelectionModel().getSelection();
		var record = selection[0];
		var currentSource = this.getSource();
		delete(currentSource[record.get('name')]);
		this.setSource(currentSource);
	},

	getData: function() {
		return this.getSource();
	}
});
