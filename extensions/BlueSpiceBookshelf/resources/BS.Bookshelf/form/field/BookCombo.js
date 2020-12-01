Ext.define('BS.Bookshelf.form.field.BookCombo', {
	extend: 'MWExt.form.field.GridPicker',
	requires: [ 'BS.Bookshelf.data.BookStore' ],

	triggerAction: 'all',
	typeAhead: true,
	displayField: 'book_displaytext',
	valueField: 'book_prefixedtext',

	gridConfig: {
		border:true,
		hideHeaders: true,
		features: [{
			ftype: 'grouping',
			groupHeaderTpl: [
				'{name:this.formatName}',
				{
					formatName: function(name) {
						var location = bs.bookshelf.storageLocationRegistry.lookup( name );
						if ( location ) {
							return location.getLabel();
						}

						return '';
					}
				}
			],
			collapsible: false
		}],
		columns: [{
			dataIndex: 'book_displaytext',
			flex: 1
		}]
	},

	initComponent: function(){

		this.store = new BS.Bookshelf.data.BookStore( {
			proxy: {
				extraParams: {
					limit: 9999 //Bad hack to avoid paging
				}
			},
			groupField: 'book_type'
		} );

		this.callParent(arguments);
	}
});
