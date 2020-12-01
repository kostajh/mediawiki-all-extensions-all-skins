Ext.define( 'BS.Expiry.flyout.form.Expiry', {
	extend: 'Ext.form.Panel',
	cls: 'bs-expiry-flyout-form',
	title: mw.message( 'bs-expiry-flyout-form-title' ).plain(),
	date: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
	articleId: mw.config.get( 'wgArticleId' ),
	fieldDefaults: {
		anchor: '100%'
	},
	initComponent: function() {
		this.on( 'dirtychange', this.onDirtyChange, this );

		this.dfDate = Ext.create( 'Ext.form.field.Date', {
			emptyText: mw.message( 'bs-expiry-date-label' ).plain(),
			value: this.date,
			minValue: new Date( ),
			name: 'df-date',
			format: "d.m.Y"
		} );
		this.taComment = Ext.create( 'Ext.form.field.TextArea', {
			emptyText: mw.message( 'bs-expiry-comment-label' ).plain(),
			value: '',
			maxLength: 255
		});
		this.hfId = Ext.create( 'Ext.form.field.Hidden', {
			value: '',
			name: 'hf-id'
		} );
		this.items = [
			this.dfDate,
			this.taComment,
			this.hfId
		];

		this.btnSave = Ext.create("Ext.button.Button", {
			id: "bs-expiry-flyout-expiry-form-save-btn",
			text: mw.message('bs-extjs-save').plain(),
			handler: this.onBtnSaveClick,
			flex: 0.5,
			scope: this
		});

		this.btnCancel = Ext.create("Ext.button.Button", {
			id: "bs-expiry-flyout-expiry-form-cancel-btn",
			text: mw.message('bs-extjs-reset').plain(),
			handler: this.onBtnCancelClick,
			flex: 0.5,
			scope: this,
			disabled: true
		});

		this.buttons = [
			this.btnSave,
			this.btnCancel
		];

		var items = this.items;
		if ( mw.config.get( 'bsgReminderRegisteredTypes', null ) && mw.config.get( 'bsgReminderRegisteredTypes' ).expiry ) {
			this.cbxCreateReminder = Ext.create( 'Ext.form.field.Checkbox', {
				fieldLabel: mw.message( 'bs-expiry-create-reminder-label' ).plain()
			});
			this.items.push( this.cbxCreateReminder );
		}
		$( document ).trigger( "BSExpiryInitCreateForm", [ this, items ] );

		this.callParent(arguments);
	},
	setData: function ( obj ) {
		if ( typeof ( obj.articleId ) !== "undefined" ) {
			this.getDataForId( obj.articleId );
			return true;
		}
		this.dfDate.setValue( obj.date );
		this.hfId.setValue( obj.id );
		this.taComment.setValue( obj.comment );
	},
	getData: function() {
		var obj = {
			//format back to unix ts
			date: new Date( this.dfDate.getValue() ).getTime() / 1000,
			id: this.hfId.getValue(),
			articleId: this.articleId,
			comment: this.taComment.getValue(),
			setReminder: !this.cbxCreateReminder ? false : this.cbxCreateReminder.getValue()
		};
		$( document ).trigger( "BSExpiryGetData", [ this, obj ] );
		return obj;
	},
	onBtnSaveClick: function( btn, e ) {
		this.fireEvent( 'save', this, this.getData() );
	},
	onBtnCancelClick: function( btn, e ) {
		this.reset();
	},
	onDirtyChange: function( sender, dirty ) {
		this.setButtonsDisabled( !dirty );
	},
	setButtonsDisabled: function( disabled ) {
		if( disabled ) {
			this.btnCancel.disable();
		} else {
			this.btnCancel.enable();
		}
	}
} );
