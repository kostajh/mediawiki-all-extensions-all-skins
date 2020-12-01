Ext.define( 'BS.ExportTables.menu.TableExport', {
	extend: 'Ext.menu.Menu',
	cls: 'bs-exporttables-menu',

	htmlTableProvider: {
		getHTMLTable: function() {
			//override me
		}
	},

	initComponent: function() {
		this.items = [
			{
				iconCls: 'icon-csv',
				text: mw.message('bs-exporttables-menu-csv').plain(),
				exportMode: 'csv',
				handler: this.onMenuItemClick,
				scope: this
			},
			{
				iconCls: 'icon-xls',
				text: mw.message('bs-exporttables-menu-xls').plain(),
				exportMode: 'xls',
				handler: this.onMenuItemClick,
				scope: this
			},
			{
				iconCls: 'icon-xlsx',
				text: mw.message('bs-exporttables-menu-xlsx').plain(),
				exportMode: 'xlsx',
				handler: this.onMenuItemClick,
				scope: this
			}
		];

		$( document ).trigger( 'BSExportTablesMenu', [ this, this.items ] );
		this.callParent( arguments );
	},

	onMenuItemClick: function( item, e ) {
		var $dfd_htmlTable = this.htmlTableProvider.getHTMLTable();
		var modeTo = item.exportMode;

		var actionUrl = bs.util.wikiGetlink(
			{
				'ue[module]': 'table2excel'
			},
			'Special:UniversalExport/'+mw.config.get('wgPageName')
		);

	$dfd_htmlTable.done(function( htmlTable ) {
		
		var form = Ext.getBody().createChild({
				tag: 'form',
				method: 'POST',
				action: actionUrl,
				//target : '_blank',
				children: [{
					tag: 'input',
					type: 'hidden',
					name: 'ModeFrom',
					value: 'html'
				}, {
					tag: 'input',
					type: 'hidden',
					name: 'ModeTo',
					value: modeTo
				}, {
					tag: 'input',
					type: 'hidden',
					name: 'content',
					value: Ext.String.htmlEncode(htmlTable)
				}]
			});

			form.dom.submit(/*{target : '_blank'}*/);
			form.remove();
		});
	}
});