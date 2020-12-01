(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.expiry' );

	bs.expiry.flyoutCallback = function( $body ) {
		var dfd = $.Deferred();
		Ext.create( 'BS.Expiry.flyout.Base', {
			renderTo: $body[0],
			userCanExpire: !!mw.config.get( 'bsgExpiryCanExpire', false ),
			userCanDeleteExpiration: !!mw.config.get( 'bsgExpiryCanDeleteExpiration', false )
		} );

		dfd.resolve();
		return dfd.promise();
	};

})( mediaWiki, jQuery, blueSpice );
