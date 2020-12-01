Ext.define( 'BS.NSFileRepoConnector.grid.Namespaces', {
	extend: 'Ext.grid.Panel',
	requires: [
		'BS.NSFileRepoConnector.store.TargetNamespaces'
	],
	viewConfig: {
		stripeRows: false
	},
	autoScroll : true,
	hideHeaders: true,
	columns: [
		{
			dataIndex: 'namespace',
			flex: 1
		}
	],
	preSelectNamespace: false,
	initComponent: function() {
		this.selModel = new Ext.selection.CheckboxModel( {
			mode: 'SINGLE',
			allowDeselect: true
		});
		this.store = new BS.NSFileRepoConnector.store.TargetNamespaces();
		this.on( 'afterLayout', function() {
			//after layout is the only way to select within the checkbox
			//selection model and do a scroll to view. this means, that the
			//store, that is filtered by namespaces needs to load twice. On load
			//and another time, when we apply this selection and do the
			//scrolling. This could cause long waiting times on systems with a
			//lot of images and may be deactived for some customers
			if( this.preSelectNamespace === false ) {
				return;
			}
			if( this.preSelectNamespace < 0 ) {
				return;
			}
			var recordIndex = this.getStore().findExact(
				'id',
				this.preSelectNamespace
			);
			if( recordIndex < 0 ) {
				return;
			}

			this.selModel.select( recordIndex );
			//scroll to selected item
			this.getView().scrollRowIntoView(
				this.getStore().getAt( recordIndex )
			);
		});
		this.callParent( arguments );
	}
});