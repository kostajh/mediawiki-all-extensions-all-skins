Ext.define("BS.BlueSpiceInsertTemplate.dialog.InsertTemplate", {
	extend: "MWExt.Dialog",
	bodyStyle: {
		backgroundColor: "#fff"
	},
	modal: true,
	width: 600,
	height: 500,
	layout: 'border',
	title: mw.message('bs-insertTemplate-dialog-title').plain(),
	afterInitComponent: function() {
		this.templateStore = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-inserttemplate-store',
			fields: ['id', 'name', 'desc', 'code' ],
			submitValue: false,
			remoteSort: false,
			remoteFilter: false,
			proxy: {
				type: 'ajax',
				url: mw.util.wikiScript('api'),
				extraParams: {
					format: 'json',
					limit: 0
				},
				reader: {
					type: 'json',
					rootProperty: 'results',
					idProperty: 'name'
				}
			},
			sortInfo: {
				field: 'name'
			}
		});

		this.templateGrid = Ext.create( 'Ext.form.ComboBox', {
			triggerAction: 'all',
			editable: true,
			minChars: 1,
			allowBlank: false,
			forceSelection: true,
			value: 'id',
			store: this.templateStore,
			displayField: 'name',
			valueField: 'id'
		});

		this.templateGrid.on( 'select', this.onRowSelect, this );

		this.syntaxTextArea = Ext.create( 'Ext.form.TextArea', {
			hideLabel: true,
			flex: 1
		});

		this.syntaxTextArea.on( 'blur', this.onSyntaxTextAreaBlur, this );

		this.syntaxPanel = Ext.create('Ext.Panel', {
			title: mw.message('bs-insertTemplate-dialog-label-modify').plain(),
			border: true,
			flex: 1,
			layout: 'fit',
			items: [ this.syntaxTextArea ]
		});

		this.previewPanel = Ext.create('Ext.Panel', {
			title: mw.message('bs-insertTemplate-dialog-label-preview').plain(),
			tools: [{
				type: 'refresh'
			}],
			border: true,
			flex: 1,
			autoScroll: true
		});

		this.pnlNorth = Ext.create('Ext.Container', {
			region: 'north',
			padding: 5,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items: [
				Ext.create( 'Ext.form.Label', { text: mw.message('bs-insertTemplate-dialog-label-choose').plain() }),
				this.templateGrid
			]
		});

		this.pnlWest = Ext.create('Ext.Container', {
			region: 'west',
			width: 250,
			padding: 5,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items: [
				this.syntaxPanel
			]
		});

		this.pnlCenter = Ext.create('Ext.Container', {
			region: 'center',
			border: false,
			padding: 5,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items:[
				this.previewPanel
			]
		});

		this.items = [
			this.pnlNorth,
			this.pnlWest,
			this.pnlCenter
		];

		this.callParent(arguments);
	},

	/**
	 *
	 * @param {Ext.form.field.TextArea} sender
	 * @param e
	 * @param eOpts
	 */
	onSyntaxTextAreaBlur: function ( sender, e, eOpts ) {
		var api = new mw.Api();
		var me = this;
		api.get({
			'action': 'parse',
			'title': mw.config.get( 'wgPageName' ),
			'text': sender.getValue()
		})
		.done( function( response, jqHXR ) {
			me.previewPanel.update( response.parse.text['*'] );
		});
	},

	getData: function() {
		this.currentData.code = this.syntaxTextArea.getValue();
		return this.currentData;
	},

	setData: function( obj ) {
		this.syntaxTextArea.setValue( obj.code );
		this.syntaxTextArea.fireEvent('blur', this.syntaxTextArea);
		this.callParent( arguments );
	},

	onRowSelect: function( combo, record ) {
		var data = {
			desc : record.get( 'desc' )
		};
		this.currentData.name = record.get( 'name' );

		this.setCommonFields( record.get( 'code' ), data );
	},

	setCommonFields: function( text, data ) {
		var desc = data.desc;
		this.previewPanel.update( desc );
		this.syntaxTextArea.setValue( text );
		this.syntaxTextArea.focus();

		var start = text.indexOf('"') + 1;
		var end = text.indexOf('"', start );

		this.syntaxTextArea.selectText(start, end);
	}
});