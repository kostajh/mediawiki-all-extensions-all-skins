Ext.define( 'MWExt.Dialog', {
	extend: 'Ext.Window',
	requires: [ 'Ext.Button', 'Ext.form.Label', 'Ext.toolbar.Toolbar' ],
	width: 600,
	closeAction: 'hide',
	title: '',
	resizable: false,
	cls: 'mwext-dialog',
	modal: true,
	draggable: false,
	layout: 'fit',
	fieldDefaults: {
		labelAlign: 'right',
		anchor:'100%'
	},
	mixins: [
		'Ext.mixin.Responsive'
	],
	responsiveConfig: {
		'width < 600': {
			maximized: true
		}
	},
	defaultFocus : 1,

	titleAlign: 'center',
	closable: false,

	bodyPadding: 5,

	constructor: function( config ) {
		//Custom Settings
		this.currentData = {};
		this.callParent(arguments);
	},

	initComponent: function() {
		this.items = this.makeMainFormPanel();
		this.header = this.makeHeader();
		this.dockedItems = this.makeDockedItems();

		this.afterInitComponent( arguments );

		this.callParent( arguments );
	},

	afterInitComponent: function() {},

	makeHeader: function() {
		return {
			items: this.makeButtons(),
			titlePosition: 1
		};
	},

	makeMainFormPanel: function() {
		this.mainFormPanel = new Ext.form.Panel({
			layout: 'anchor',
			defaultFocus : 1,
			fieldDefaults: this.fieldDefaults,
			items: this.makeItems()
		});
		this.mainFormPanel.getForm().on( 'validitychange', function( form, valid, eOpts ) {
			if( valid ) {
				this.btnOK.enable();
			}
			else {
				this.btnOK.disable();
			}
		}, this );
		return this.mainFormPanel;
	},

	show: function () {
		this.setLoading( false );
		this.callParent( arguments );
	},

	afterShow: function() {
		this.setY( window.scrollY + this.getY() );
		this.callParent( arguments );
	},

	onBtnOKClick: function () {
		var me = this;
		this.setLoading( true );
		var steps = this.makeOkSteps();

		if ( me.fireEvent( 'ok', me, me.getData(), steps ) ) {
			$.when.apply( $, steps ).then( function() {
				me.setLoading( false );
				me.close();
			});
		}
	},

	makeOkSteps: function() {
		return [
			this.makeDefaultOkStep()
		];
	},

	makeDefaultOkStep: function () {
		var dfd = $.Deferred();
		dfd.resolve(); //This is just a default. Nothing to do here.
		return dfd.promise();
	},

	onBtnCancelClick: function() {
		var me = this;
		this.resetData();
		var steps = this.makeCancelSteps();

		if ( me.fireEvent( 'cancel', me, steps ) ) {
			$.when.apply( $, steps ).then( function() {
				me.close();
			});
		}
	},

	makeCancelSteps: function() {
		return [
			this.makeDefaultCancelStep()
		];
	},

	makeDefaultCancelStep: function () {
		var dfd = $.Deferred();
		dfd.resolve(); //This is just a default. Nothing to do here.
		return dfd.promise();
	},

	getData: function(){
		this.currentData = this.mainFormPanel.getValues();
		return this.currentData;
	},

	setData: function( obj ){
		this.currentData = obj;
		this.mainFormPanel.setValues( obj );
	},

	resetData: function() {
	},

	makeId: function( part ) {
		return this.getId() + '-' + part;
	},

	makeItems: function() {
		return [];
	},

	makeButtons: function() {
		this.btnOK = new Ext.Button({
			text: mw.message('extjsbase-btn-done').plain(),
			id: this.getId()+'-btn-ok',
			cls: 'mwext-button mwext-ok mwext-primary mwext-progressive'
		});
		this.btnOK.on( 'click', this.onBtnOKClick, this );

		this.btnCancel = new Ext.Button({
			text: mw.message('extjsbase-btn-cancel').plain(),
			id: this.getId()+'-btn-cancel',
			cls: 'mwext-button mwext-cancel mwext-safe'
		});
		this.btnCancel.on( 'click', this.onBtnCancelClick, this );

		return [
			this.btnCancel,
			this.btnOK
		];
	},

	makeDockedItems: function() {
		var footerButtons = this.makeFooterButtons();
		if( footerButtons.length === 0 ) {
			return null;
		}

		return [
			new Ext.toolbar.Toolbar({
				dock: 'bottom',
				ui: 'footer',
				defaults: {
					minWidth: this.minButtonWidth
				},
				items: footerButtons
			})
		];
	},

	makeFooterButtons: function() {
		return [];
	}
});

