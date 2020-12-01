Ext.define( 'BS.Expiry.flyout.grid.ChangesSinceExpired', {
	extend: 'Ext.grid.Panel',
	requires: ['BS.store.ApiRecentChanges'],
	pageSize: 10,
	expirationDate: '',
	title: mw.message( 'bs-expiry-edit-grid-title' ).plain(),
	emptyText:  mw.message( 'bs-expiry-edit-grid-empty' ).plain(),
	initComponent: function() {
		this.store = new BS.store.ApiRecentChanges({
			pageSize: this.pageSize,
			filters: [{
				property: 'page_prefixedtext',
				type: 'string',
				comparison: 'eq',
				value: mw.config.get( 'wgTitle' )
			},{
				property: 'page_namespace',
				type: 'numeric',
				comparison: 'eq',
				value: mw.config.get( 'wgNamespaceNumber' )
			},{
				property: 'raw_timestamp',
				type: 'date',
				comparison: 'gt',
				value: this.expirationDate
			}]
		});

		this.colAggregatedInfo = Ext.create( 'Ext.grid.column.Template', {
			id: 'expired-aggregated',
			sortable: false,
			width: 400,
			tpl: '<span>{user_link} {timestamp}</span><br/><span>{comment_text}</span>',
			flex: 1
		} );

		this.colUserLink = Ext.create( 'Ext.grid.column.Column', {
			id: 'user_link',
			header: mw.message( 'bs-expiry-edit-grid-header-username' ).plain(),
			sortable: true,
			dataIndex: 'user_link',
			hidden: true
		} );

		this.colEditDate = Ext.create( 'Ext.grid.column.Column', {
			id: 'timestamp',
			header: mw.message('bs-expiry-edit-grid-header-date').plain(),
			sortable: true,
			dataIndex: 'timestamp',
			hidden: true,
			filter: {
				type: 'date'
			}
		} );

		this.colComment = Ext.create( 'Ext.grid.column.Column', {
			id: 'comment_text',
			header: mw.message('bs-expiry-edit-grid-header-comment').plain(),
			sortable: false,
			hidden: true,
			dataIndex: 'comment_text',
			filter: {
				type: 'string'
			}
		});

		this.columns = [
			this.colAggregatedInfo,
			this.colUserLink,
			this.colEditDate,
			this.colComment
		];

		this.bbar = new Ext.toolbar.Paging( {
			store: this.store
		} );

		this.callParent( arguments );
	}
} );
