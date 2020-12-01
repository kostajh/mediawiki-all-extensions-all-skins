( function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.calumma.recentchanges' );

	bs.calumma.recentchanges.flyoutTriggerCallback = function( $body ) {
		var dfd = $.Deferred();
		var previewCaptureNotDefault = mw.config.get(
			'bsgArticlePreviewCaptureNotDefault',
			false
		);
		mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
			Ext.Loader.setPath( 'BS.Calumma', mw.config.get( 'stylepath' )
				+ '/BlueSpiceCalumma/resources/js/BS.Calumma' );
			Ext.create( 'BS.Calumma.flyout.RecentChanges', {
				renderTo: $body[0],
				defaultTab: previewCaptureNotDefault === false ? 'gridviewpanel' : ''
			} );

			dfd.resolve();
		} );
		return dfd.promise();
	};

})( mediaWiki, jQuery, blueSpice );
