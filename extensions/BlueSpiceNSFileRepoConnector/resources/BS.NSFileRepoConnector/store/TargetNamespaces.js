Ext.define( 'BS.NSFileRepoConnector.store.TargetNamespaces', {
	extend: 'BS.store.LocalNamespaces',

	constructor: function( cfg ) {
		cfg = cfg || {
			includeAll: false
		};

		cfg.excludeIds = bs.nsfrc.getInvalidFileNamespacesForReading();

		this.callParent( [cfg] );
	}
});