( function( mw, $, d ) {
BsExportTablesHTMLTableProvider = {
	_makeHTMLTableProvider: function( sender ) {
	return {
		getHTMLTable: function() {
			var dfd = $.Deferred();
			var lastRequest = sender.strMain.getProxy().getLastRequest();
			var params = lastRequest.getParams();

			//This is ugly... unfortunately most AJAX interfaces can not
			//handle requests without those parameters
			params.page = 1;
			params.limit = 9999999;
			params.start = 0;

			var url = lastRequest.getUrl(); //TODO: Modify _dc flag!

			Ext.Ajax.request({
				url:url,
				params: params,
				success: function( response ){
					var resp = Ext.decode( response.responseText );
					var proxy = sender.strMain.getProxy();
					var reader = proxy.getReader();
					var rows = resp[reader._rootProperty];
					var columns = sender.grdMain.columns;
					var row = null;
					var col = null;
					var value = '';
					var $table = $('<table>');
					var $row = null;
					var $cell = null;
					var record = null;

					$row = $('<tr>');
					$table.append($row);
					for( var i = 0; i < columns.length; i++ ) {
						col = columns[i];
						if( col instanceof Ext.grid.ActionColumn )
							continue;

						if( col.hidden === true )
							continue;

						$cell = $('<td>');
						$row.append( $cell );
						$cell.append( col.header || col.text );
					}

					for( var i = 0; i < rows.length; i++ ) {
						row = rows[i];
						$row = $('<tr>');
						record = new sender.strMain.model( row );
						$table.append($row);

						for( var j = 0; j < columns.length; j++ ) {
							col = columns[j];
							if( col instanceof Ext.grid.ActionColumn )
								continue;

							if( col.hidden === true )
								continue;

							$cell = $('<td>');
							$row.append( $cell );

							if( col.renderer && Ext.isFunction( col.renderer ) ) {
								value = col.renderer(
									row[col.dataIndex],
									{}, //Cell meta... we don't have any
									record,
									i,
									j,
									sender.strMain,
									sender.grdMain.getView()
								);
							}
							else {
								value = row[col.dataIndex];
							}
							$cell.append( value );
						}
					}

					dfd.resolve( '<table>' + $table.html() + '</table>' );
				}
			});
			return dfd;
		}
	};
}
};

$( d ).on('BSCRUDPanelInitComponent', function( e, sender ) {
	if( typeof(BS.CRUDGridPanel) === "undefined" || sender instanceof BS.CRUDGridPanel === false ) {
		return;
	}

	var provider = sender.getHTMLTable
		? sender
		: BsExportTablesHTMLTableProvider._makeHTMLTableProvider( sender )
	;
	if( sender.tbar ) {
		sender.tbar.add( '->' );
		sender.tbar.add( {
			text: mw.message('bs-exporttables-menu').plain(),
			menu: Ext.create('BS.ExportTables.menu.TableExport',{
				htmlTableProvider: provider
			})
		} );
		return;
	}
	if( sender.bbMain ) {
		sender.bbMain.add( '->' );
		sender.bbMain.add( {
			text: mw.message('bs-exporttables-menu').plain(),
			menu: Ext.create('BS.ExportTables.menu.TableExport',{
				htmlTableProvider: provider
			})
		} );
		return;
	}
});

$( d ).on('BSPanelInitComponent', function( e, sender ) {
	if( typeof sender === 'undefined' || !sender.getHTMLTable ) {
		return;
	}
	var provider = sender;

	if( sender.tbar ) {
		sender.tbar.add( '->' );
		sender.tbar.add( {
			text: mw.message('bs-exporttables-menu').plain(),
			menu: Ext.create('BS.ExportTables.menu.TableExport',{
				htmlTableProvider: provider
			})
		} );
		return;
	}
	if( sender.bbMain ) {
		sender.bbMain.add( '->' );
		sender.bbMain.add( {
			text: mw.message('bs-exporttables-menu').plain(),
			menu: Ext.create('BS.ExportTables.menu.TableExport',{
				htmlTableProvider: provider
			})
		} );
		return;
	}
});

$( d ).on('BSPermissionManagerAfterInitComponent', function( e, sender ) {
	var provider = sender.getHTMLTable
		? sender
		: BsExportTablesHTMLTableProvider._makeHTMLTableProvider( sender )
	;

	sender.tbar.push( {
		text: mw.message('bs-exporttables-menu').plain(),
		menu: Ext.create('BS.ExportTables.menu.TableExport',{
			htmlTableProvider: provider
		})
	} );
});

$( d ).on ( 'BSFlaggedRevsConnectorGridInit', function( e, grid ) {
	grid.tbar = [ '->', {
		text: mw.message('bs-exporttables-menu').plain(),
		menu: Ext.create('BS.ExportTables.menu.TableExport', {
			htmlTableProvider: grid
		} )
	} ];
} );

} )( mediaWiki, jQuery, document );
