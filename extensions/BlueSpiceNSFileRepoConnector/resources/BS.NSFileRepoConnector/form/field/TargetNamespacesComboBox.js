Ext.define( 'BS.NSFileRepoConnector.form.field.TargetNamespacesComboBox', {
	extend: 'Ext.form.field.ComboBox',
	requires: [
		'BS.NSFileRepoConnector.store.NSFRNamespaces'
	],

	fieldLabel: mw.message('bs-nsfrc-namespacestore-label').plain(),
	emptyText: mw.message('bs-nsfrc-namespacecombo-emptytext').plain(),

	queryMode: 'local',
	displayField: 'nsText',
	valueField: 'nsIdx',

	allOption: false,

	initComponent: function() {
		this.store = new BS.NSFileRepoConnector.store.NSFRNamespaces( {
			allOption: this.allOption
		} );
		this.callParent( arguments );
	}
});