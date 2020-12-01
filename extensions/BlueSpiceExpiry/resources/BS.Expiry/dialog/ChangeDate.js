Ext.define( "BS.Expiry.dialog.ChangeDate", {
	extend: "MWExt.Dialog",
	modal: true,
	title: mw.message( 'bs-expiry-dialog-title' ).plain(),
	makeItems: function () {
		this.dfDate = Ext.create( 'Ext.form.field.Date', {
			fieldLabel: mw.message( 'bs-expiry-date-label' ).plain(),
			margin: '0 5 0 0',
			value: new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ),
			minValue: new Date(),
			labelAlign: 'right',
			name: 'df-date',
			format: "d.m.Y"
		} );
		this.items = [
			this.dfDate
		];
		var items = this.items;
		$( document ).trigger( "BSExpiryChangeDateDialogInit", [ this, items ] );

		return this.items;
	},
	setData: function () {
		this.dfDate.setValue( new Date( mw.config.get( 'DefaultReminderPeriod' ) * 1000 ) );
	},
	getData: function () {
		var obj = {
			date: new Date( this.dfDate.getValue() ).getTime() / 1000
		};
		$( document ).trigger( "BSExpiryChangeDateDialogGetData", [ this, obj ] );
		return obj;
	}
});