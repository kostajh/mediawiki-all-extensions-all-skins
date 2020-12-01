(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.insertcategory.flyout' );

	bs.insertcategory.flyout.makeItems = function( flyout, basicData ) {
		var categoryEditor = Ext.create( 'BS.InsertCategory.panel.CategoryEditor', {
			parentFlyout: flyout,
			pageId: mw.config.get( 'wgArticleId' ),
			allCategories: mw.config.get( 'wgCategories' ),
			userCanEdit: basicData.userCanEdit
		} );

		return {
			centerLeft: [
				categoryEditor
			]
		}
	};

})( mediaWiki, jQuery, blueSpice );
