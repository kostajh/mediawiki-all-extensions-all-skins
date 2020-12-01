Ext.define( 'BS.Readers.grid.RevisionReaders', {
	extend: 'Ext.grid.Panel',
	requires: [ 'BS.store.BSApi' ],
	title: mw.message( 'bs-readers-revision-flyout-title' ).plain(),
	cls: 'bs-readers-flyout',
	maxWidth: 600,
	plugins: 'gridfilters',
	articleId: mw.config.get( 'wgArticleId' ),
	readersLimit: mw.config.get( 'bsgReadersNumOfReaders' ),
	initComponent: function() {

		this.store =  new BS.store.BSApi({
			apiAction: 'bs-readers-revision-readers-store',
			fields: [
				'readers_page_id',
				'readers_user_name',
				'readers_rev_id',
				'datetime',
				'rev_datetime',
				'readers_user_id'
			],
			proxy: {
				extraParams: {
					limit: this.readersLimit
				}
			},
			filters: [{
				property: 'readers_page_id',
				type: 'numeric',
				comparison: 'eq',
				value: this.articleId
			}],
			pageSize: this.readersLimit
		} );


		this.columns = [
			{
				text: mw.message( 'bs-readers-flyout-header-read-by' ).plain(),
				sortable: true,
				dataIndex: 'readers_user_name',
				flex: 25 / 100
			},
			{
				text: mw.message( 'bs-readers-flyout-header-revision-id' ).plain(),
				sortable: true,
				dataIndex: 'readers_rev_id',
				filter: 'numeric',
				flex: 15 / 100
			},
			{
				text: mw.message( 'bs-readers-flyout-header-revision-date' ).plain(),
				sortable: false,
				dataIndex: 'rev_datetime',
				filter: false,
				flex: 30 / 100
			},
			{
				text: mw.message( 'bs-readers-flyout-header-read-on' ).plain(),
				sortable: true,
				dataIndex: 'readers_ts',
				renderer: function( value, metaData, record, rowIndex, colIndex, store ) {
					return record.get( 'datetime' );
				},
				flex: 30 / 100
			},
		];

		this.bbar = new Ext.toolbar.Paging( {
			store: this.store
		} );

		this.callParent( arguments );
	}
} );
