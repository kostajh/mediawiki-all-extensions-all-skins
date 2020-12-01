Ext.define( 'BS.Expiry.flyout.dataview.NextExpiry', {
	extend: 'Ext.DataView',
	requires: [ 'BS.store.BSApi', 'BS.Expiry.flyout.model.NextExpiry' ],
	articleId: mw.config.get( 'wgArticleId' ),
	expirationStatus: '',
	expirationDate: '',
	userCanExpire: false,
	userCanDeleteExpiration: false,
	initComponent: function() {
		this.store = new BS.store.BSApi( {
			apiAction: 'bs-expiry-store',
			autoLoad: true,
			remoteSort: true,
			model: 'BS.Expiry.flyout.model.NextExpiry',
			sorters: [{
				property: 'expiry_date',
				direction: 'ASC'
			}],
			filters: [{
				property: 'article_id',
				type: 'numeric',
				comparison: 'eq',
				value: this.articleId
			}]
		} );

		this.store.on( 'load', function( store, records, successful, operation, eOpts ) {
			var record = records[0];
			if( !record ) {
				this.fireEvent( 'expirationDataSet', this, {} );
				return;
			}
			this.expirationStatus = record.data.expiration_status;
			this.expirationDate = record.data.expiry_date;
			this.fireEvent( 'expirationDataSet', this, this.getExpirationInfo() );
		}, this );

		var unexpire = '', updateexpiry = '';
		var store = this.store;
		if ( this.userCanDeleteExpiration ) {
			unexpire = "<li><a href='#' class='bs-expiry-unexpire' data-expid='{id}'>"
				+ mw.message( 'bs-expiry-do-unexpire-article' ).plain()
				+ "</a></li>";

		}
		if ( this.userCanExpire ) {
			updateexpiry = "<li><a href='#' class='bs-expiry-updateexpirydate' "
				+ "data-expiry='{expiry_json}'>"
				+ mw.message( 'bs-expiry-do-updateexpirydate-article' ).plain()
				+ "</a></li>";
		}
		this.itemTpl = new Ext.XTemplate(
			"<div class='bs-expiry-flyout-next-{expiration_status}'>",
			"<span>{expiry_message}</span>&nbsp",
			"<ul>",
			unexpire,
			updateexpiry,
			"</ul>",
			"<br/><span class='bs-expiry-flyout-next-comment'>{comment_message}</span></div>"
		);
		this.emptyText = mw.message( 'bs-expiry-flyout-expiry-not-set' ).plain();
		this.callParent( arguments );
	},

	getExpirationInfo: function() {
		return {
			status: this.expirationStatus,
			date: this.expirationDate
		};
	}
} );
