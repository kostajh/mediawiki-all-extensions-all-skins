(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.articleinfo' );

	bs.articleinfo.flyoutCallback = function( $body, data ) {
		var dfd = $.Deferred();
		mw.loader.using( data.rlModuleDependencies )
			.done( function() {
				Ext.create( 'BS.ArticleInfo.flyout.Base', {
					renderTo: $body[0],
					basicData: data,
					makeItemCallbacks: data.makeItemsCallbacks,
					lastEditedTime: data.lastEditedTime || {},
					lastEditedUser: data.lastEditedUser || {},
					pageCategoryLinks: data.categoryLinks || {},
					templateLinks: data.templateLinks || {},
					hasSubpages: data.hasSubpages || false
				} );

				dfd.resolve();
			} );

		return dfd.promise();
	};

} )( mediaWiki, jQuery, blueSpice );
