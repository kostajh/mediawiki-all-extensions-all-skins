Ext.define( 'BS.NamespaceCSS.panel.Manager', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],

	initComponent: function() {
		this.strMain = new BS.store.BSApi( {
			apiAction: 'bs-namespacecss-store',
			fields: [
				'ns_id',
				'ns_name',
				'source_page',
				'source_page_link'
			]
		});

		this.columns = [{
			id: 'ns_name',
			header: mw.message(
				'bs-namespacecss-specialmanager-label-namespacename'
			).plain(),
			dataIndex: 'ns_name',
			sortable: true,
			filter: {
				type: 'string'
			}
		},{
			id: 'source_page',
			header: mw.message(
				'bs-namespacecss-specialmanager-label-sourcepage'
			).plain(),
			dataIndex: 'source_page',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				return record.get( 'source_page_link' )
			},
			sortable: true,
			filter: {
				type: 'string'
			}
		}];

		this.colMainConf.columns = this.columns;
		this.callParent( arguments );
	},
	makeActionColumn: function( cols ) {
		return false;
	},
	makeTbar : function() {
		return false;
	}
} );