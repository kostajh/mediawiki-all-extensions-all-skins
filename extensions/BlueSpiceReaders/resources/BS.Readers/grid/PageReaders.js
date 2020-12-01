Ext.define( 'BS.Readers.grid.PageReaders', {
	extend: 'Ext.grid.Panel',
	requires: [ 'BS.store.BSApi' ],
	title: mw.message( 'bs-readers-flyout-title' ).plain(),
	cls: 'bs-readers-flyout',
	maxWidth: 600,
	articleId: mw.config.get( 'wgArticleId' ),
	readersLimit: mw.config.get( 'bsgReadersNumOfReaders' ),
	hideHeaders: true,
	initComponent: function() {

		this.store =  new BS.store.BSApi({
			apiAction: 'bs-readers-page-readers-store',
			fields: [ 'user_image_html', 'readers_page_id', 'readers_user_name' ],
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

		this.colAggregatedInfo = Ext.create( 'Ext.grid.column.Template', {
			id: 'readers-aggregated',
			sortable: false,
			width: 400,
			tpl: "<div class='bs-readers-flyout-grid-item'>" +
				"{user_image_html}" +
				"<span>{readers_user_name}</span></div>",
			flex: 1
		} );


		this.columns = [
			this.colAggregatedInfo,
		];

		this.bbar = new Ext.toolbar.Paging( {
			store: this.store
		} );

		this.callParent( arguments );
	}
} );
