(function( mw, $, d, undefined ){
	$(d).on( 'click', '#ca-pageassignments a, a#ca-pageassignments', function( e ) {
		e.preventDefault();

		var curPageId = mw.config.get( 'wgArticleId' );
		var me = this;

		var api = new mw.Api();
		api.postWithToken( 'csrf', {
			'action': 'bs-pageassignment-tasks',
			'formatversion': 2,
			'task': 'getForPage',
			'taskData': JSON.stringify( {
				pageId: curPageId
			} )
		}).done(function( response, xhr ){
			if( response.success ) {
				mw.loader.using( 'ext.bluespice.extjs' ).done( function() {
					Ext.onReady( function() {
						var dlg = Ext.create( 'BS.PageAssignments.dialog.PageAssignment', {
							pageId: curPageId,
							pageAssignments: response.payload
						} );
						dlg.show( me );
					});
				} );
			}
		});

		return false;
	} );
})( mediaWiki, jQuery, document );