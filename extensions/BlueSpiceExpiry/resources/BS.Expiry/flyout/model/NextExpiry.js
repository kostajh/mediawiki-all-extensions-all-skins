Ext.define( 'BS.Expiry.flyout.model.NextExpiry', {
	extend: 'Ext.data.Model',
	fields: [
		{ name: 'days_remaining', type: 'string', convert: function( val, record ) {
				var today = new Date();
				var remDate = new Date( record.data.expiry_date );
				var msTime = remDate.getTime() - today.getTime();
				var daysRemaining = Math.ceil( msTime / ( 1000 * 60 * 60 * 24 ) );
				return daysRemaining;
		} },
		{ name: 'expiration_status', type: 'string', convert: function( val, record ) {
				if( record.data.days_remaining <= 0 ) {
					return 'expired';
				}
				return 'current';
		} },
		{ name: 'expiry_message', type: 'string', convert: function( val, record ) {
				if( record.data.expiration_status === 'expired' ) {
					return mw.message( 'bs-expiry-flyout-next-expired' ).plain();
				}
				return mw.message(
					'bs-expiry-flyout-next-current',
					record.data.days_remaining,
					record.data.expiry_date
				).parse();
		} },
		{ name: 'comment_message', type: 'string', convert: function( val, record ) {
				return mw.message(
					'bs-expiry-flyout-next-comment',
					record.data.exp_comment
				).plain();
			} },
		{ name: 'id', type: 'number' },
		{ name: 'expiry_date', type: 'string' },
		{ name: 'article_id', type: 'string' },
		{ name: 'exp_comment', type: 'string' },
		{ name: 'expiry_json', type: 'string', convert: function( val, record ) {
			return JSON.stringify( record.data );
		} }
	]
} );
