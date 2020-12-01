Ext.define( 'BS.PageAssignments.flyout.form.NewAssignment', {
	extend: 'Ext.form.Panel',
	requires: [ 'Ext.button.Button' ],
	cls: 'bs-pageassignments-flyout-form',
	title: mw.message('bs-pageassignments-flyout-form-title').plain(),
	articleId: mw.config.get('wgArticleId'),
	maxWidth: 400,
	layout: 'anchor',
	fieldDefaults: {
		anchor: '100%'
	},
	initComponent: function () {
		this.currentData = null;

		this.assignableStore = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: mw.util.wikiScript( 'api' ),
				reader: {
					type: 'json',
					rootProperty: 'results'
				},
				extraParams: {
					action: 'bs-pageassignable-store',
					format: 'json',
					context: JSON.stringify( bs.util.getCAIContext() )
				}
			},
			fields: [
				'text',
				'id',
				'anchor',
				'pa_assignee_type',
				'pa_assignee_key',
				'pa_position'
			],
			remoteSort: true,
			autoLoad: true
		} );

		this.cbAssignables = new Ext.form.field.ComboBox({
			emptyText:  mw.message( "bs-extjs-combo-box-default-placeholder" ).plain(),
			displayField: 'text',
			valueField: 'id',
			minChars: 1,
			listConfig: {
				getInnerTpl: function() {
					return '{["<span class=\'bs-icon-" + values.pa_assignee_type + " bs-typeicon\'></span>"+values.text]}';
				}
			},
			store: this.assignableStore
		});
		this.cbAssignables.on( 'select', this.onAssignableChanged, this );

		this.items = [
			this.cbAssignables
		];

		this.btnAdd = new Ext.button.Button( {
			id: this.getId() + "-add-btn",
			text: mw.message('bs-extjs-add').plain(),
			handler: this.onBtnAddClick,
			flex: 0.5,
			scope: this,
			disabled: true
		});

		this.buttons = [
			this.btnAdd
		]

		this.callParent( arguments );
	},

	getData: function() {
		return this.currentData;
	},

	setData: function( data ) {
		this.currentData = data;
		this.cbAssignables.setValue( data.id );
	},

	onBtnAddClick: function( btn, e ) {
		this.fireEvent( 'add', this, this.getData() );
	},

	onAssignableChanged: function( control, record, eOpts ) {
		this.currentData = record.data;
		this.btnAdd.enable();
	}
} );
