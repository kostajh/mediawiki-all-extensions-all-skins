Ext.define( 'BS.Readers.panel.RevisionReaders', {
	extend: 'Ext.grid.Panel',
	requires: [ 'BS.store.BSApi' ],
	id: 'bs-revision-readers-panel',
	initComponent: function () {
		this.makeStore();

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

		this.bbar = new Ext.PagingToolbar( {
			store : this.store,
			displayInfo : true
		} );

		this.tbar =  new Ext.toolbar.Toolbar({
			xtype: 'toolbar',
			displayInfo: true
		} );

		this.plugins = [
			'gridfilters'
		];

		$( document ).trigger( 'BSPanelInitComponent', [ {
			getHTMLTable: this.getHTMLTable.bind( this ),
			tbar: this.tbar
		} ] );

		this.callParent( arguments );
	},

	makeStore: function() {
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
			filters: [{
				property: 'readers_page_id',
				type: 'numeric',
				comparison: 'eq',
				value: mw.config.get( 'bsRevisionReadersPageId' )
			}]
		} );

		return this.store;
	},

	getHTMLTable: function() {
		var dfd = $.Deferred();
		var store = this.makeStore();
		var proxy = store.getProxy();
		proxy.extraParams.limit = 999999;
		store.setProxy( proxy );
		store.load( { callback: function( records, operation, success ) {
				if ( !operation.success ) {
					return dfd.reject ( operation );
				}
				var $table = $( '<table>' );
				var $row = $( '<tr>' );

				var $cell = $( '<td>' );
				$cell.append(
					mw.message( 'bs-readers-flyout-header-read-by' ).text()
				);
				$row.append( $cell );

				$cell = $( '<td>' );

				$cell.append(
					mw.message( 'bs-readers-flyout-header-revision-id' ).plain()
				);
				$row.append( $cell );

				$cell.append(
					mw.message( 'bs-readers-flyout-header-revision-date' ).plain()
				);
				$row.append( $cell );

				$cell.append(
					mw.message( 'bs-readers-flyout-header-read-on' ).plain()
				);
				$row.append( $cell );

				$table.append( $row );

				for( var rid = 0; rid < records.length; rid++ ) {
					var record = records[rid];
					$row = $( '<tr>' );

					$cell = $( '<td>' );
					$cell.append( record.data.readers_user_name );
					$row.append( $cell );

					$cell = $( '<td>' );
					$cell.append( record.data.readers_rev_id );
					$row.append( $cell );

					$cell = $( '<td>' );
					$cell.append( record.data.rev_datetime );
					$row.append( $cell );

					$cell = $( '<td>' );
					$cell.append( record.data.datetime );
					$row.append( $cell );

					$table.append( $row );
				}

				dfd.resolve( '<table>' + $table.html() + '</table>' );
			} } );


		return dfd;
	}
} );
