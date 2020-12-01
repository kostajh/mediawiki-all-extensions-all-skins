Ext.define( 'BS.PageAssignments.flyout.Base', {
	extend: 'BS.flyout.TwoColumnsBase',
	requires: [ 'BS.PageAssignments.flyout.form.NewAssignment' ],
	makeCenterTwoItems: function() {
		if( !this.assigneesGrid ) {
			this.assigneesGrid = Ext.create( 'BS.PageAssignments.flyout.grid.AssigneesPanel', {
				userCanAssign: this.userCanAssign
			} );
			this.assigneesGrid.on( 'delete', this.deleteAssignment, this );
		}

		return [
			this.assigneesGrid
		];
	},

	makeCenterOneItems: function() {
		if( !this.assignmentForm ) {
			this.assignmentForm = Ext.create( 'BS.PageAssignments.flyout.form.NewAssignment', {} );
			this.assignmentForm.on( 'add', this.addAssignment, this );
		}

		var result = [];
		if ( this.userCanAssign ) {
			result.push( this.assignmentForm );
		}
		return result;
	},

	makeTopPanelItems: function() {
		return [];
	},

	makeBottomPanelItems: function() {
		if( !this.btnManager ) {
			this.btnManager = Ext.create('Ext.Button', {
				text: mw.message( 'bs-pageassignments-flyout-manager-btn-label' ).plain()
			});
		}
		this.btnManager.on( 'click', this.onBtnManagerClick );
		return [
			this.btnManager
		]
	},

	onBtnManagerClick: function() {
		var url = mw.util.getUrl( "Special:PageAssignments/" );
		window.location = url;
	},

	getCurrentAssigneeIds: function() {
		var dfd = $.Deferred();
		var assigneeIds = [];
		this.assigneesGrid.getAssignees()
			.done( function( assignees ) {
				for( var i = 0; i < assignees.length; i++ ) {
					assigneeIds.push( assignees[i].id );
				}
				dfd.resolve( assigneeIds );
			} );
		return dfd;
	},

	addAssignment: function( form, data ) {
		var me = this;

		this.getCurrentAssigneeIds().done( function( assigneeIds ) {
			assigneeIds.push( data.id );
			var $dfd = me.doSaveAssignments( data.pa_page_id, assigneeIds );
			$dfd.fail(function( sender, data, resp ){
				bs.util.alert(
					'bs-pa-error',
					{
						text: resp.message || ''
					}
				);
			})
			.done(function( sender ){
				form.reset();
				form.btnAdd.disable();
				me.assigneesGrid.updateStoreData();
			});
		} );


	},

	deleteAssignment: function( pageId, assigneeToRemove ) {
		var me = this;

		this.getCurrentAssigneeIds().done( function( assigneeIds ) {
			assigneeIds.splice( assigneeIds.indexOf( assigneeToRemove ), 1 );

			var $dfd = me.doSaveAssignments( pageId, assigneeIds );
			$dfd.fail( function( sender, data, resp ){
				bs.util.alert(
					'bs-pa-error',
					{
						text: resp.message
					}
				);
			} ).done(function( sender ){
					me.assigneesGrid.updateStoreData();
				});
		} );
	},

	doSaveAssignments: function( pageId, assigneeIds ) {
		var action = Ext.create( 'BS.PageAssignments.action.ApiTaskEdit', {
			pageId: pageId,
			pageAssignments: assigneeIds
		});

		return action.execute();
	}
} );
