Ext.define( 'BS.NSFileRepoConnector.store.NSFRNamespaces', {
	extend: 'Ext.data.Store',
	requires: ['BS.NSFileRepoConnector.model.NSFRNamespaces'],
	model: 'BS.NSFileRepoConnector.model.NSFRNamespaces',
	autoLoad: true,
	allOption: true,
	load: function() {
		this.clearData();

		var me = this;
		bs.api.tasks.exec( 'nsfrc', 'getPossibleNamespaces' )
		.done( function ( response ) {
			if( me.allOption ) {
				me.add({
					nsIdx: -1,
					nsText: mw.message('bs-ns_all').plain()
				});
			}

			for( var i in response.payload ) {
				me.add({
					nsIdx: i,
					nsText: response.payload[i] === ''
						? mw.message('bs-ns_main').plain()
						: response.payload[i]
				});
			}
		});
	}
});