Ext.define( 'BS.Calumma.dialog.NewSubPage', {
	extend: 'MWExt.Dialog',
	closeAction: 'destroy',

	makeItems: function () {
		this.setTitle( mw.message( 'bs-action-new-subpage-title', this.basePage ).text() );
		this.cbPageName = Ext.create( 'Ext.form.field.ComboBox', {
			id: this.getId() + '-pagename',
			fieldLabel: mw.message( 'bs-action-new-subpage-text' ).plain(),
			enableKeyEvents: true,
			valueField: 'text',
			queryMode: 'local',
			store: new Ext.data.JsonStore( {
				proxy: {
					type: 'ajax',
					url: mw.util.wikiScript( 'api' ),
					extraParams: {
						format: 'json',
						action: 'bs-wikisubpage-treestore',
						node: this.basePage
					},
					reader: {
						type: 'json',
						rootProperty: 'children',
						idProperty: 'id',
						totalProperty: 'total'
					}
				},
				autoLoad: true,
				fields: [ 'text', 'id' ]
			} )
		} );
		this.cbPageName.on( 'keypress', this.onPageNameKeypress, this );

		return [
			this.cbPageName
		];
	},

	onPageNameKeypress: function ( combo, e, eOpts ) {
		if ( e.charCode === 13 ) {
			this.cbPageName.select( this.cbPageName.getRawValue() );
			this.onBtnOKClick();
		}
	},

	getData: function () {
		return this.cbPageName.getRawValue();
	},

	show: function() {
		this.callParent(arguments);
		this.cbPageName.focus();
	}
} );
