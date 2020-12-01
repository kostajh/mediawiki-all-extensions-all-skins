( function( $, d ) {
	Ext.define( 'BS.Statistics.panel.List', {
		extend: 'Ext.grid.Panel',
		headerPosition: 'bottom',

		bsPayload: {},
		pageSize: 10,

		initComponent: function() {
			this.setTitle( this.bsPayload.label );

			this.store = new Ext.data.JsonStore( {
				fields: this.bsPayload.data.fields,
				data: this.bsPayload.data.list.items,
				pageSize: this.pageSize,
				remoteSort: true,
				proxy: {
					type: 'memory',
					enablePaging: true,
					extraParams: {
						limit: this.pageSize
					}
				}
			} );

			var columns = this.bsPayload.data.columns;
			columns[0].width = 250;
			columns[0].flex = 0;

			this.columns = {
				items: columns,
				defaults: {
					flex: 1
				}
			};

			// Enable page size modification
			this.pageSizeCombo = this.makePageSizeCombo();

			this.bbar = new Ext.PagingToolbar( {
				store: this.store,
				dock: 'bottom',
				displayInfo: true,
				items: [
					this.pageSizeCombo
				]
			} );
			this.tbar = new Ext.toolbar.Toolbar( {} );
			this.dockedItems = [ this.tbar, this.bbar ];

			// Make table exportable
			$( d ).trigger( 'BSPanelInitComponent', [ this ] );

			return this.callParent( arguments );
		},

		makePageSizeCombo: function() {
			var combo = new Ext.form.ComboBox({
				fieldLabel: mw.message ( 'bs-statistics-paging-page-size' ).plain(),
				labelAlign: 'right',
				autoSelect: true,
				forceSelection: true,
				triggerAction: 'all',
				typeAhead: false,
				mode: 'local',
				store: new Ext.data.SimpleStore( {
					fields: ['text', 'value'],
					data: [
						['10', 10],
						['20', 20],
						['50', 50],
						['100', 100],
						['200', 200],
						['500', 500]
					]
				} ),
				value: this.pageSize,
				labelWidth: 120,
				flex: 2,
				valueField: 'value',
				displayField: 'text'
			});

			combo.on( 'select', this.onSelectPageSize, this );

			return combo;
		},

		onSelectPageSize: function( sender, event ) {
			var pageSize = sender.getValue();
			this.store.pageSize = pageSize;
			this.store.proxy.extraParams.limit = pageSize;
			this.store.load();
		},

		getHTMLTable: function() {
			var data = this.bsPayload.data,
				dfd = $.Deferred(),
				$table = $( '<table>' ),
				$row = $( '<tr>' ), i, $cell, fields = [];

			for ( i = 0; i < data.columns.length; i++ ) {
				$cell = $( '<td>' );
				$cell.append( data.columns[i].header );
				$row.append( $cell );
			}
			$table.append( $row );

			for ( i = 0; i < data.fields.length; i++ ) {
				fields.push( data.fields[i].name );
			}

			for ( i = 0; i < data.list.items.length; i++ ) {
				$row = $( '<tr>' );
				for ( var key in data.list.items[i] ) {
					if ( !data.list.items[i].hasOwnProperty( key ) ) {
						continue;
					}
					if ( fields.indexOf( key ) === -1 ) {
						continue;
					}
					$cell = $( '<td>' );
					$cell.append( data.list.items[i][key] );
					$row.append( $cell );
				}
				$table.append( $row );
			}

			dfd.resolve( '<table>' + $table.html() + '</table>' );

			return dfd;
		}
	} );
} )( jQuery, document );
