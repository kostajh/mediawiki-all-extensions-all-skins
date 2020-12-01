Ext.define('BS.Bookshelf.MetaKeyComboBox', {
	extend: 'Ext.form.field.ComboBox',
	queryMode: 'local',
	triggerAction: 'all',
	displayField: 'name',
	valueField: 'value',
	allowBlank: false,

	//Custom settings
	bsData: {},

	initComponent: function() {
		var realData = [];
		for( var key in this.bsData ) {
			var name = key;
			if( this.bsData[key].displayName ) {
				name = this.bsData[key].displayName;
			}
			var set = {
				name: name,
				value: key
			};
			realData.push(set);
		}

		this.store = Ext.create('Ext.data.JsonStore', {
			fields: [ this.displayField, this.valueField ],
			data: realData
		});
		this.callParent(arguments);
	}
});
