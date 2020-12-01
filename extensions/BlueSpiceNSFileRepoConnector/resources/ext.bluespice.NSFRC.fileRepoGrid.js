$( document ).bind( 'BS.grid.FileRepo.initComponent', function( event, sender, items ){
	var excludeIds = bs.nsfrc.getInvalidFileNamespacesForReading();

	sender.colNSIdx = Ext.create( 'Ext.grid.column.Column', {
		sortable: false,
		filter: {
			type: 'numeric'
		},
		hidden: true,
		hideable: false,
		dataIndex: 'file_nsfr_repo_idx'
	});
	sender.columns.items.push( sender.colNSIdx );

	sender.cbNamespaces = Ext.create(
		'BS.form.NamespaceCombo',
		{
			includeAll: true,
			excludeIds: excludeIds
		}
	);

	sender.cbNamespaces.on( 'select', function( combo, records, eOpts ) {
		var selectedIdx = combo.getValue();
		var column = sender.getColumnByDataIndex( 'file_nsfr_repo_idx' );
		if( !column ) {
			return;
		}

		if( selectedIdx === -99 ) {
			column.filter.setActive( false );
			return;
		}
		column.filter.setValue( { eq: selectedIdx } );
		column.filter.setActive( true );
	} );

	sender.tbTop.add( sender.cbNamespaces );

	sender.on( 'afterrender', function() {
		var currentNamespaceId = mw.config.get( 'wgNamespaceNumber' );
		if( excludeIds.indexOf( currentNamespaceId ) !== -1 ) {
			return;
		}
		sender.cbNamespaces.setValue( currentNamespaceId );
		sender.colNSIdx.filter.setValue( { eq: currentNamespaceId } );
		sender.colNSIdx.filter.setActive( true );
	} );
});