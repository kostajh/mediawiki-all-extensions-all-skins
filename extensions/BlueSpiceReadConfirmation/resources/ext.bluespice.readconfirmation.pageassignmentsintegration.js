( function( d, mw, $, bs, undefined ) {

	var activated = function( ns ) {
		var namespaces = mw.config.get( 'bsgReadConfirmationActivatedNamespaces', [] );
		for( var i = 0; i < namespaces.length; i++ ) {
			if ( parseInt( ns ) !== parseInt( namespaces[i] ) ) {
				continue;
			}
			return true;
		}
		return false;
	};

	$( d ).on( 'BSPageAssignmentsManagerPanelInit', function( e, sender, cols, fields, actions ){
		fields.push( 'all_assignees_have_read' );

		cols.push({
			text: mw.message('bs-readconfirmation-column-read').plain(),
			dataIndex: 'all_assignees_have_read',
			flex: 0,
			width: 70,
			align: 'center',
			sortable: true,
			filter:{
				type: 'boolean'
			},
			renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
				if( activated( record.get( 'page_namespace' ) ) ) {
					return mw.html.element( 'span', {
						class: 'bs-rc-col ' + ( value ? 'bs-icon-checkmark-circle yes' : 'bs-icon-cancel-circle no' )
					});
				}
				return '<em>' + mw.message( 'bs-readconfirmation-disabled-ns-short' ).plain() +'</em>';
			}
		});

		actions.push({
			tooltip: mw.message('bs-readconfirmation-action-log').plain(),
			glyph: true, //Needed to have the "BS.override.grid.column.Action" render an <span> instead of an <img>
			scope: this,
			handler: function( view, rowIndex, colIndex,item, e, record, row ) {
				window.open(
					bs.util.wikiGetlink( {
						page: record.get( 'page_prefixedtext' ),
						type: 'bs-readconfirmation'
					}, 'Special:Log' )
				);
			},
			getClass: function( value, meta, record ) {
				return "bs-icon-text bs-extjs-actioncolumn-icon bs-readconfirmation-action-log";
			},
			isDisabled: function( view, rowIndex, colIndex, item, record  ) {
				return !activated( record.get( 'page_namespace' ) );
			}
		});

		actions.push({
			tooltip: mw.message('bs-readconfirmation-action-remind').plain(),
			glyph: true, //Needed to have the "BS.override.grid.column.Action" render an <span> instead of an <img>
			scope: this,
			handler: function( view, rowIndex, colIndex,item, e, record, row ) {
				bs.util.confirm( 'bs-rc', {
					textMsg: 'bs-readconfirmation-action-remind-confirm'
				}, {
					ok: function() {
						bs.api.tasks.exec( 'readconfirmation', 'remind', {
							pageId: record.get( 'page_id' )
						} );
					}
				});
			},
			getClass: function( value, meta, record ) {
				return "bs-icon-bell bs-extjs-actioncolumn-icon bs-readconfirmation-action-remind";
			},
			isDisabled: function( view, rowIndex, colIndex, item, record  ) {
				if( !record.get( 'assignments' ) || record.get( 'assignments' ).length < 1 ) {
					return true;
				}
				return record.get( 'all_assignees_have_read' ) || !activated( record.get( 'page_namespace' ) );
			}
		});
	});

	$( d ).on( 'BSPageAssignmentsOverviewPanelInit', function( e, sender, cols, fields, actions ){
		fields.push( 'read_confirmation' );

		cols.push({
			text: mw.message('bs-readconfirmation-column-read-at').plain(),
			xtype: 'datecolumn',
			format: 'Y-m-d H:i', //Doesn't work with custom renderer :(
			dataIndex: 'read_confirmation',
			sortable: true,
			filter:{
				type: 'date'
			},
			renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
				if( !value ) {
					metaData.tdCls += ' bs-rc-not-read';
					return '<em>' + mw.message('bs-readconfirmation-not-read').plain() +'</em>';
				}
				if( value === 'disabled' ) {
					return '<em>' + mw.message('bs-readconfirmation-disabled-ns').plain() +'</em>';
				}

				var date = Ext.Date.parse( value, "YmdHis");
				var renderer = Ext.util.Format.dateRenderer( 'Y-m-d, H:i' );

				return renderer( date );
			}
		});
	});
} )( document, mediaWiki, jQuery, blueSpice );
