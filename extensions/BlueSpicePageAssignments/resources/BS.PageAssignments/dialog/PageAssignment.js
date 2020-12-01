Ext.define( 'BS.PageAssignments.dialog.PageAssignment', {
	extend: 'MWExt.Dialog',
	requires: [ 'BS.form.field.ItemList', 'BS.PageAssignments.action.ApiTaskEdit' ],
	title: mw.message('bs-pageassignments-dlg-title').plain(),

	pageId: -1,
	pageAssignments: [],

	makeItems: function() {
		this.itmList = new BS.form.field.ItemList({
			labelAlign: 'top',
			model: 'BS.PageAssignments.model.Assignable',
			apiStore: 'bs-pageassignable-store',
			typeField: 'pa_assignee_type',
			apiFields: [
				'text',
				'id',
				'anchor',
				'pa_assignee_type',
				'pa_assignee_key',
				'pa_position'
			],
			apiStoreConfig: {
				proxy: {
					extraParams: {
						context: JSON.stringify( {
							wgArticleId: this.pageId
						} )
					}
				}
			},
			minChars: 1
		});

		this.itmList.setValue( this.pageAssignments );

		return [
			this.itmList
		];
	},

	onBtnOKClick: function() {
		var me = this;
		me.setLoading( true );

		var assignees = this.itmList.getValue();
		var assigneeIds = [];
		for( var i = 0; i < assignees.length; i++ ) {
			assigneeIds.push( assignees[i].id );
		}

		var action = new BS.PageAssignments.action.ApiTaskEdit({
			pageId: this.pageId,
			pageAssignments: assigneeIds
		});

		var $dfd = action.execute();
		$dfd.fail(function( sender, data, resp ){
			bs.util.alert(
				'bs-pa-error',
				{
					text: resp.message
				}
			);
			me.setLoading( false );
		})
		.done(function( sender ){
			me.setLoading( false );
			if ( me.fireEvent( 'ok', me, action ) ) {
				me.close();
			}
		});
	}
} );