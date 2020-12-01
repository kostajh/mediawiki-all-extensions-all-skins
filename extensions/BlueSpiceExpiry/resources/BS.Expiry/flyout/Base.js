Ext.define( 'BS.Expiry.flyout.Base', {
	extend: 'BS.flyout.TwoColumnsBase',
	requires: [
		'BS.Expiry.flyout.form.Expiry',
		'BS.Expiry.flyout.dataview.NextExpiry',
		'BS.Expiry.flyout.grid.ChangesSinceExpired'
	],
	userCanExpire: false,
	userCanDeleteExpiration: false,
	makeTopPanelItems: function() {
		if( !this.nextExpiry ) {
			this.nextExpiry = new BS.Expiry.flyout.dataview.NextExpiry( {
				userCanExpire: this.userCanExpire,
				userCanDeleteExpiration: this.userCanDeleteExpiration
			} );
			this.nextExpiry.on( 'expirationDataSet', this.onExpirationDataSet, this );
		}
		return [
			this.nextExpiry
		];
	},

	makeBottomPanelItems: function() {
		this.btnManager = new Ext.Button( {
			text: mw.message( 'bs-expiry-flyout-manager-button-label' ).plain()
		});
		this.btnManager.on( 'click', this.onBtnManagerClick );

		return [
			this.btnManager
		];
	},

	onBtnManagerClick: function() {
		var url = mw.util.getUrl( "Special:Expiry" );
		window.location = url;
	},

	saveExpiry: function( form, data ) {
		var me = this;
		bs.api.tasks.exec(
			'expiry',
			'saveExpiry',
			data
		)
		.done( function(){
			me.nextExpiry.store.reload();
			me.remove( form, true );
			me.updateLayout();
		} );
		$( document ).trigger( "BSExpiryEditOk", [ this, data ] );
	},

	onExpirationDataSet: function( view, data ) {
		if( $.isEmptyObject( data ) && this.userCanExpire ) {
			this.expiryForm = new BS.Expiry.flyout.form.Expiry( {} );
			this.expiryForm.on( 'save', this.saveExpiry, this );

			this.add( this.expiryForm );
			this.updateLayout();
			return;
		}

		if( data.status !== 'expired' ) {
			return;
		}

		this.add( new BS.Expiry.flyout.grid.ChangesSinceExpired( {
			expirationDate: data.date,
			region: 'center'
		} ) );
		this.updateLayout();
	}
} );
