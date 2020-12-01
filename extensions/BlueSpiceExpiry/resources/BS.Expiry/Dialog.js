Ext.define( "BS.Expiry.Dialog", {
	extend: "MWExt.Dialog",
	modal: true,
	title: mw.message( 'bs-expiry-dialog-title' ).plain(),
	makeItems: function () {
		this.dfDate = Ext.create( 'Ext.form.field.Date', {
			fieldLabel: mw.message( 'bs-expiry-date-label' ).plain(),
			margin: '0 5 0 0',
			value: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
			minValue: new Date( ),
			labelAlign: 'right',
			name: 'df-date',
			format: "d.m.Y"
		} );
		this.taComment = Ext.create( 'Ext.form.field.TextArea', {
			fieldLabel: mw.message( 'bs-expiry-comment-label' ).plain(),
			labelAlign: 'right',
			value: '',
			maxLength: 255,
			margin: '0 5 0 0',
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
		var items = this.items;
		if ( mw.config.get( 'bsgReminderRegisteredTypes', null ) && mw.config.get( 'bsgReminderRegisteredTypes' ).expiry ) {
			this.cbxCreateReminder = Ext.create( 'Ext.form.field.Checkbox', {
				fieldLabel: mw.message( 'bs-expiry-create-reminder-label' ).plain()
			});
			this.items.push( this.cbxCreateReminder );
		}
		$( document ).trigger( "BSExpiryInitCreateForm", [ this, items ] );

		return this.items;
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
	getDataForId: function ( iArticleId ) {
		var me = this;
		var api = new mw.Api();
		api.postWithToken( 'csrf', {
			action: 'bs-expiry-tasks',
			task: 'getDetailsForExpiry',
			taskData: Ext.encode( {
				'articleId': iArticleId
			} )
		})
		.fail(function( protocol, response ) {
			bs.util.alert(
				'bs-expiry-delete-error-unknown',
				{
					text: response.exception
				}
			);
		})
		.done(function( response, xhr ){
			if ( typeof ( response.payload.id ) !== "undefined" ) {
				me.setData( response.payload );
			}
		});
	},
	getData: function () {
		var obj = {
			//format back to unix ts
			date: new Date( this.dfDate.getValue() ).getTime() / 1000,
			id: this.hfId.getValue(),
			articleId: mw.config.get( 'wgArticleId' ),
			comment: this.taComment.getValue(),
			setReminder: !this.cbxCreateReminder ? false : this.cbxCreateReminder.getValue()
		};
		$( document ).trigger( "BSExpiryGetData", [ this, obj ] );
		return obj;
	}
});