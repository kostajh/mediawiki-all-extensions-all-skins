(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.bookshelf' );

	bs.bookshelf.flyoutTriggerCallback = function( $body ) {
		var dfd = $.Deferred();
		Ext.create( 'BS.Bookshelf.flyout.GeneralBooks', {
			renderTo: $body[0]
		} );

		dfd.resolve();
		return dfd.promise();
	};

})( mediaWiki, jQuery, blueSpice );
