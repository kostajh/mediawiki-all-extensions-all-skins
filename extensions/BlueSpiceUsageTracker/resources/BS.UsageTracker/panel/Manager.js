Ext.define( 'BS.UsageTracker.panel.Manager', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],

	initComponent: function() {

		this._gridCols = [
			{
				text: mw.message( 'bs-usagetracker-col-identifier' ).plain(),
				dataIndex: 'identifier',
				sortable: true,
				flex: 1,
				filter: {
					type: 'string'
				},
			},
			{
				text: mw.message( 'bs-usagetracker-col-desc' ).plain(),
				dataIndex: 'description',
				sortable: true,
				width: '50%',
				filter: {
					type: 'string'
				},
			},
			{
				text: mw.message( 'bs-usagetracker-col-last-updated' ).plain(),
				dataIndex: 'updateDate',
				sortable: true,
				flex: 1,
				filter: {
					type: 'date'
				},
			},
			{
				text: mw.message( 'bs-usagetracker-col-count' ).plain(),
				dataIndex: 'count',
				sortable: true,
				width: '20px',
				filter: {
					type: 'numeric'
				},
			}
		];

		this._storeFields = [
			'identifier',
			'description',
			'descriptionKey',
			'updateDate',
			'count',
			'type'
		];

		this.callParent( arguments );
	},

	makeGridColumns: function(){
		this.colMainConf.columns = this._gridCols;
		return this.colMainConf.columns;
		return this.callParent( arguments );
	},

	makeRowActions: function() {
		return [];
	},

	makeMainStore: function() {
		this.strMain = new BS.store.BSApi({
			apiAction: 'bs-usagetracker-store',
			fields: this._storeFields
		});
		return this.callParent( arguments );
	},

	makeTbarItems: function() {
		return [];
	}
});