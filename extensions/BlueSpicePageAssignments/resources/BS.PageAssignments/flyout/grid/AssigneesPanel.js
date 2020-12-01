Ext.define( 'BS.PageAssignments.flyout.grid.AssigneesPanel', {
	extend: 'Ext.grid.Panel',
	requires: [ 'BS.PageAssignments.flyout.model.AssigneeModel' ],
	title: mw.message( 'bs-pageassignments-flyout-grid-title' ).plain(),
	cls: 'bs-pageassignments-flyout-grid',
	maxWidth: 600,
	pageSize : 5,
	hideHeaders: true,
	articleId: mw.config.get( 'wgArticleId' ),
	userCanAssign: false,
	initComponent: function() {

		this.store = Ext.create( 'Ext.data.Store', {
			model: 'BS.PageAssignments.flyout.model.AssigneeModel',
			pageSize: this.pageSize,
			proxy: {
				type: 'memory',
				enablePaging: true
			}
		});
		this.updateStoreData();


		this.colAggregatedInfo = Ext.create( 'Ext.grid.column.Template', {
			id: 'assignees-aggregated',
			sortable: false,
			width: 400,
			tpl: "<div class='bs-pageassignments-flyout-grid-item item-type-{pa_assignee_type}'>" +
			"{assignee_image_html}" +
			"<span>{text}</span> " +
			"<span class='assignee-type'>{pa_assignee_type}</span></div>",
			flex: 1
		} );

		this.colUnAssign = Ext.create( 'Ext.grid.column.Action', {
			flex:0,
			width: 30,
			items: [{
				iconCls: 'bs-extjs-actioncolumn-icon bs-icon-cross destructive',
				glyph: true,
				handler: function( view, rowIndex, colIndex,item, e, record, row ) {
					var id = record.id;
					this.fireEvent( 'delete', this.articleId, id );
				},
				scope: this
			}],
			hideable: false,
			sortable: false
		});

		this.columns = [
			this.colAggregatedInfo,
		];
		if ( this.userCanAssign ) {
			this.columns.push( this.colUnAssign );
		}

		this.bbar = new Ext.toolbar.Paging( {
			store: this.store
		} );

		this.callParent( arguments );
	},

	getAssignees: function () {
		var dfd = $.Deferred();
		this.getApiData()
			.done( function( response, xhr ){
				var data = [];
				if( response.success === true ) {
					data = response.payload;
				}
				dfd.resolve( data );
			})
			.fail( function() {
				dfd.reject();
			} );
		return dfd;
	},

	updateStoreData: function() {
		this.getApiData().done( function( response, xhr ){
			var data = [];
			if( response.success === true ) {
				data = response.payload;
			}

			this.store.getProxy().setData( data );
			this.store.loadPage( 1 );
		}.bind( this ) );
	},

	getApiData: function() {
		var api = new mw.Api();
		return api.postWithToken( 'csrf', {
			'action': 'bs-pageassignment-tasks',
			'task': 'getForPage',
			'taskData': JSON.stringify( {
				pageId: this.articleId
			} )
		});
	}
} );
