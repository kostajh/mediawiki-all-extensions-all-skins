$(document).on( 'BSMultiUploadDialogMakeItems', function(e, sender, items) {
	var exNamespaces, defaultIndex, configuredPrefix, prefixParts,
		potentialNamespacePrefix, namespaces, namespaceId, potentialIndex,
		strippedPrefix;
	exNamespaces = bs.nsfrc.getInvalidFileNamespacesForEditing();

	sender.cbNmsps = Ext.create( 'BS.form.NamespaceCombo',{
		excludeIds: exNamespaces,
		fieldLabel: mw.message( 'bs-nsfrc-namespacestore-label' ).plain()
	});

	defaultNamespaceId = mw.config.get( 'wgNamespaceNumber' );
	if( exNamespaces.indexOf( defaultIndex ) !== -1 ) {
		defaultNamespaceId = 0;
	}
	defaultIndex = sender.cbNmsps.getStore().find( 'id', defaultNamespaceId );
	if( defaultIndex === -1 ) {
		defaultIndex = 0;
	}

	//Check field "defaultFileNamePrefix" for namespace prefix and set value of
	//namespace selector combobox accordingly
	configuredPrefix = sender.uploadPanelCfg.defaultFileNamePrefix;
	if( configuredPrefix && configuredPrefix !== '' ) {
		prefixParts = configuredPrefix.split( ':' );
		potentialNamespacePrefix = prefixParts[0].replace( / /g, '_' );
		namespaces = mw.config.get( 'wgNamespaceIds' );
		namespaceId = namespaces[potentialNamespacePrefix.toLowerCase()];
		potentialIndex = sender.cbNmsps.getStore().find( 'id', namespaceId );
		if( potentialIndex !== -1 ) {
			defaultIndex = potentialIndex;

			//Strip namespace prefix, as it is being set in hookhandler for
			//"BSMultiUploadDialogMakeAction"
			strippedPrefix = configuredPrefix.replace( / /g, '_' );
			strippedPrefix = strippedPrefix.replace( potentialNamespacePrefix + ':', '' );
			sender.fsUploadDetails.defaultFileNamePrefix = strippedPrefix.replace( /_/g, ' ' );
		}
	}

	sender.cbNmsps.select( sender.cbNmsps.getStore().getAt( defaultIndex ) );
	sender.fsUploadDetails.collapse();

	items.unshift( sender.cbNmsps );
});

$(document).on( 'BSMultiUploadDialogMakeAction', function(e, sender, actionCfg) {
	var formattedNamespaces = mw.config.get( 'wgFormattedNamespaces' );
	var prefix = formattedNamespaces[sender.cbNmsps.getValue()];
	if( prefix ) {
		actionCfg.uploadApiMeta.filename = prefix + ':' + actionCfg.uploadApiMeta.filename;
	}
});