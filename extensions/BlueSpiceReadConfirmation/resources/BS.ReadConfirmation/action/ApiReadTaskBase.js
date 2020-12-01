Ext.define('BS.ReadConfirmation.action.ApiReadTaskBase', {
	extend: 'BS.action.Base',

	//Custom Settings
	pageId: -1,
	pageTitle: '',
	taskKey: '',

	execute: function() {
		var dfd = $.Deferred();
		this.actionStatus = BS.action.Base.STATUS_RUNNING;
		var data = {
			pageId: this.pageId
		};
		mw.hook( 'readconfirmation.check.request.before' ).fire(data);

		this.doApiConfirmRead( dfd, data );

		return dfd.promise();
	},

	doApiConfirmRead: function( dfd, data ) {
		var me = this;

		var api = new mw.Api();
		bs.api.tasks.execSilent( 'readconfirmation', this.taskKey, data )
		.fail(function( code, errResp ){
			me.actionStatus = BS.action.Base.STATUS_ERROR;
			dfd.reject( me, data, errResp );
		})
		.done(function( resp, jqXHR ){
			if( !resp.success ) {
				me.actionStatus = BS.action.Base.STATUS_ERROR;
				dfd.reject( me, data, resp );
				return;
			}

			me.actionStatus = BS.action.Base.STATUS_DONE;
			dfd.resolve( me );
		});
	}
});