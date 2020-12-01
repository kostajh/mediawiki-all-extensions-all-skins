Ext.define( 'BS.InsertMagic.MWVEWindow', {
	extend: 'MWExt.Dialog',
	id: 'bs-InsertMagic-dlg-window',
	modal: true,
	width: 600,
	height: 400,
	layout: 'border',
	preSelectedType: 'tag',

	afterInitComponent: function() {
		this.setTitle( mw.message('bs-insertmagic-dlg-title').plain() );

		this.tagsStore = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-insertmagic-data-store',
			fields: ['id', 'type', 'name', 'desc', 'code', 'mwvecommand', 'examples', 'helplink' ],
			submitValue: false,
			remoteSort: false,
			remoteFilter: true,
			filters: [{
				property: 'mwvecommand',
				value: '',
				comparison: 'neq',
				type: 'string'
			}],
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

		this.tagsGrid = Ext.create('Ext.grid.Panel', {
			title: '',
			id: 'bs-InsertMagic-grid-tag',
			sm: Ext.create( 'Ext.selection.RowModel', { singleSelect: true }),
			store: this.tagsStore,
			layout: 'fit',
			loadMask: true,
			columns: [
				{
					id: 'name',
					sortable: true,
					dataIndex: 'name'
				}],
			forceFit: true, //HINT: http://stackoverflow.com/questions/6545719/extjs-grid-how-to-make-column-width-100
			border: true,
			columnLines: false,
			enableHdMenu: false,
			stripeRows: true,
			hideHeaders: true,
			flex: 1,
			height: 150
		});
		this.tagsGrid.on( 'select', this.onRowSelect, this );

		this.descPanel = Ext.create('Ext.Panel', {
			id: 'bs-InsertMagic-panel-desc',
			border: true,
			flex: 1,
			autoScroll: true,
			bodyPadding: 5
		});

		this.pnlWest = Ext.create('Ext.Container', {
			region: 'west',
			width: 250,
			layout: {
				//HINT: http://dev.sencha.com/deploy/ext-3.3.1/examples/form/vbox-form.js
				type: 'vbox',
				align: 'stretch' // Child items are stretched to full width
			},
			items: [
				Ext.create( 'Ext.form.Label', { text: mw.message('bs-insertmagic-label-first').plain() }),
				this.tagsGrid
			]
		});

		this.pnlCenter = Ext.create('Ext.Container', {
			region: 'center',
			border: false,
			padding: '0 0 8 5',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items:[
				new Ext.form.Label( {
					text: mw.message( 'bs-insertmagic-label-desc' ).plain()
				} ),
				this.descPanel
			]
		} );

		this.pnlMain = new Ext.panel.Panel( {
			region: 'center',
			border: false,
			layout: {
				type: 'border'
			},
			bodyStyle: {
				background: 'none'
			},
			items: [
				this.pnlWest,
				this.pnlCenter
			]
		} );

		this.items = [
			this.pnlMain
		];
		this.callParent(arguments);
	},

	getData: function() {
		return this.currentData;
	},

	onRowSelect: function( grid, record, index, eOpts ) {
		var data = {
			desc : record.get( 'desc' ),
			type : record.get( 'type' ),
			code : record.get( 'code' ),
			mwvecommand : record.get( 'mwvecommand' ),
			helplink : record.get( 'helplink' ),
			examples : record.get( 'examples' )
		};
		this.currentData.type = data.type;
		this.currentData.name = record.get( 'name' );
		this.currentData.mwvecommand = record.get( 'mwvecommand' );
		this.currentData.code = record.get( 'code' );

		this.setCommonFields( '', data );
	},

	setCommonFields: function( text, data ) {
		var desc = data.desc;
		if ( typeof( data.examples ) !== "undefined" && data.examples != '' ) {
			desc = desc
					+ '<br/><br/><strong>'
					+ mw.message( 'bs-insertmagic-label-examples' ).plain()
					+ '</strong>';
			for ( var i = 0; i < data.examples.length; i++ ) {
				desc = desc + '<br/><br/>';
				var example = data.examples[i];
				if ( typeof( example.label ) !== "undefined" && example.label != '' ) {
					desc = desc
						+ $( '<div>', { text: example.label } ).wrap( '<div/>' ).parent().html();
				};
				if ( typeof( example.code ) !== "undefined" && example.code != '' ) {
					desc = desc
						+ $( '<code>', { style: 'white-space:pre-wrap;', text: example.code } ).wrap( '<div/>' ).parent().html();
				}
			}
		}
		if ( typeof( data.helplink ) !== "undefined" && data.helplink != '' ) {
			desc = desc
					+ '<br/><br/><strong>'
					+ mw.message( 'bs-insertmagic-label-see-also' ).plain()
					+ '</strong><br/><br/>'
					+ $( '<a>', { href: data.helplink, target: '_blank', text: data.helplink } ).wrap( '<div/>' ).parent().html();
		}
		this.descPanel.update( desc );
	}
});