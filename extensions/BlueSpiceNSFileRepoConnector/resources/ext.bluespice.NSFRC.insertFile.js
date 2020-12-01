$(document).bind('BSInsertFileInsertBaseDialogAfterInit', function( event, sender, items ){
	//The store, that is filtered by namespaces needs to load twice. On load
	//and another time, when we apply this selection. This could cause long
	//waiting times on systems with a lot of images and may be deactived for
	//some customers
	var preSelect = mw.config.get(
		'bsNSFileRepoConnectorFileInsertNoPreselect',
		mw.config.get( 'wgNamespaceNumber', false )
	);
	var gdNmsps = Ext.create('BS.NSFileRepoConnector.grid.Namespaces', {
		bodyCls: 'bs-nsfrc-namespaces',
		region: 'west',
		collapsible: true,
		title: mw.message('bs-nsfrc-namespacestore-label').plain(),
		width: 175,
		preSelectNamespace: preSelect
	});

	items.push(
		gdNmsps
	);

	var origFilterFieldWidth = sender.sfFilter.width; //getWidth() is bad because element is not rendered yet
	sender.sfFilter.setWidth( 350 ); // :(
	gdNmsps.on( 'expand', function() {
		sender.sfFilter.setWidth( 350 );
	});
	gdNmsps.on( 'collapse', function() {
		sender.sfFilter.setWidth( origFilterFieldWidth );
	});
	gdNmsps.on( 'select', function( grid, record, index, eOpts ) {
		var repoFilter = sender.gdImages.filters.getFilter( 'file_nsfr_repo_idx' );
		if( !repoFilter ) {
			//Just using 'sender.gdImages.filters.addFilter' will not work
			//because it needs an appropriate column to be available
			repoFilter = sender.gdImages.filters.filters.add(
				new Ext.ux.grid.filter.NumericFilter( {
					active: true,
					dataIndex: 'file_nsfr_repo_idx'
				} )
			);
		}
		repoFilter.setActive( true );
		repoFilter.setValue( { eq: record.get('id') } );

		//'deselect' event occurs just before 'select'. If the store is
		//already loading this new filter will not be applied! Thererfore we
		//cancel the current loading process
		Ext.Ajax.abort( sender.gdImages.getStore().lastRequest );
		//'autoReload' of FiltersFeature doesn't work here, because we add the
		//filter _after_ initialization
		sender.gdImages.getStore().loadPage( 1 );
	});
	gdNmsps.on( 'deselect', function( grid, record, index, eOpts ) {
		var repoFilter = sender.gdImages.filters.getFilter( 'file_nsfr_repo_idx' );
		repoFilter.setActive( false );
		sender.gdImages.getStore().loadPage( 1 );
		sender.gdImages.getStore().lastRequest = Ext.Ajax.getLatest();
	});
});

$(document).bind('BSUploadPanelInitComponent', function( event, sender, panelItems, detailsItems ){
	var excludeIds = bs.nsfrc.getInvalidFileNamespacesForEditing();
	var currentNamespace = mw.config.get( 'wgNamespaceNumber' );

	var tfFileName = sender.tfFileName;
	var fakeTf = Ext.create('Ext.form.field.Text', {
		name: 'fakefilename',
		fieldLabel: mw.message( 'bs-nsfrc-unprefixedfilename-label' ).plain()
	});

	var nsFRCcb = Ext.create( 'BS.form.NamespaceCombo', {
		excludeIds: excludeIds,
		fieldLabel: mw.message( 'bs-nsfrc-namespacestore-label' ).plain()
	} );

	if( excludeIds.indexOf( currentNamespace ) === -1 ) {
		nsFRCcb.setValue( currentNamespace );
	}

	var fUpdateTfField = function(item, newValue, oldValue, eOpts) {
		var fileExtension = '';
		var nsText = '';
		var pos = tfFileName.getValue().lastIndexOf('.');
		if( pos !== -1 ) {
			fileExtension = tfFileName.getValue().substring( pos );
		}
		if( nsFRCcb.getValue() > 0 ) {
			nsText = nsFRCcb.getDisplayValue() + ':';
		}
		tfFileName.setValue(
			nsText +
			fakeTf.getValue() +
			fileExtension
		);
	};

	fakeTf.on( 'change', fUpdateTfField );
	nsFRCcb.on( 'change', fUpdateTfField );
	sender.fuFile.on( 'change', function(field, value, eOpts) {
		//Remove path info
		value = value.replace(/^.*?([^\\\/:]*?\.[a-z0-9]+)$/img, "$1");
		value = value.replace(/\s/g, "_");
		var pos = value.lastIndexOf('.');
		if( pos !== -1 ) {
			value = value.substring( 0, pos );
		}
		if( mw.config.get('bsIsWindows') ) {
			value = value.replace(/[^\u0000-\u007F]/gmi, ''); //Replace Non-ASCII
		}

		fakeTf.setValue(value);
		fakeTf.fireEvent('change', fakeTf, value);
	});

	tfFileName.setReadOnly( true );

	panelItems.splice(1, 0, fakeTf );
	panelItems.splice(1, 0, nsFRCcb );
});