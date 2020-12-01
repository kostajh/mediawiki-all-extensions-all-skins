Ext.define( "BS.Expiry.PanelDialog", {
	extend: "BS.Expiry.Dialog",
	modal: true,
	makeItems: function () {
		this.cbTargetPage = Ext.create( 'BS.form.field.TitleCombo', {
			fieldLabel: mw.message( 'bs-expiry-article-label' ).plain(),
			labelAlign: 'right'
		});
		this.callParent( arguments );
		this.items.unshift( this.dfDate );
		this.items.unshift( this.taComment );
		this.items.unshift( this.cbTargetPage );
		this.items.unshift( this.hfId );
		//$( document ).trigger( "BSReminderInitCreateForm", [ this, this.items ] );
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
		this.cbTargetPage.setValue( obj.page_title );
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
		var articleId = false;
		if ( this.cbTargetPage.getValue() ) {
			articleId = this.cbTargetPage.getValue().get( 'page_id' );
		}
		var obj = {
			//format back to unix ts
			date: new Date( this.dfDate.getValue() ).getTime() / 1000,
			id: this.hfId.getValue(),
			articleId: articleId,
			comment: this.taComment.getValue(),
			setReminder: !this.cbxCreateReminder ? false : this.cbxCreateReminder.getValue()
		};
		$( document ).trigger( "BSExpiryGetData", [ this, obj ] );
		return obj;
	}
});