Ext.define( 'BS.WatchList.window.MyWatchlist', {
	extend: 'MWExt.Dialog',
	requires: [ 'BS.store.BSApi', 'MWExt.form.field.Search' ],
	width: 600,
	minHeight: 300,
	title: mw.message( 'bs-watchlist-window-watchlist-title' ).plain(),
	cls: 'bs-mywatchlist-window',

	makeItems: function() {
		this.pnlIntro = {
			html: mw.message( 'bs-watchlist-window-watchlist-intro' ).parse(),
			bodyPadding: 5
		};

		this.strWatchlist = new BS.store.BSApi( {
			apiAction: 'bs-watchlist-store',
			fields: [
				'page_link',
				'page_prefixedtext',
				'has_unread_changes',
				'unread_changes_diff_revid',
				'is_talk_page'
			],
			sorters: {
				property: 'has_unread_changes',
				direction: 'DESC'
			}
		} );

		this.sfFilter = new MWExt.form.field.Search( {
			fieldLabel: mw.message( 'bs-watchlist-grid-watchlist-label-filter' ).plain(),
			flex: 1,
			store: this.strWatchlist,
			paramName: 'page_prefixedtext',
			listeners: {
				change: function ( field, newValue, oldValue, eOpts ) {
					field.onTrigger2Click();
					return true;
				}
			}
		} );

		this.gdWatchlist = new Ext.grid.Panel( {
			maxHeight: 600,
			store: this.strWatchlist,
			plugins: 'gridfilters',
			columns: [ {
				header: mw.message( 'bs-watchlist-grid-watchlist-column-link-header' ).plain(),
				dataIndex: 'page_prefixedtext',
				flex: 1,
				renderer: this.renderPagePrefixedText
			},
			{
				header: mw.message( 'bs-watchlist-grid-watchlist-column-hasunreadchanges-header' ).plain(),
				dataIndex: 'has_unread_changes',
				filterable: true,
				renderer: this.renderHasUnreadChanges,
				filter: {
					type: 'boolean',
					operator: 'eq'
				}
			},
			{
				header: mw.message( 'bs-watchlist-grid-watchlist-column-istalkpage-header' ).plain(),
				dataIndex: 'is_talk_page',
				hidden: true,
				filterable: true,
				filter: {
					type: 'boolean',
					operator: 'eq',
					value: false
				}
			},
			new Ext.grid.column.Action({
				header: mw.message( 'bs-extjs-actions-column-header' ).plain(),
				flex: 0,
				width: 100,
				cls: 'bs-extjs-action-column',
				items: [{
					iconCls: 'bs-extjs-actioncolumn-icon bs-icon-cross destructive',
					glyph: true, //Needed to have the "BS.override.grid.column.Action" render an <span> instead of an <img>,
					tooltip: mw.message( 'bs-extjs-delete' ).plain(),
					handler: this.onActionRemoveClick,
					scope: this
				}],
				menuDisabled: true,
				hideable: false,
				sortable: false
			})],
			bbar: new Ext.PagingToolbar({
				store : this.strWatchlist,
				displayInfo : true
			}),
			dockedItems: [
				new Ext.toolbar.Toolbar( {
					dock: 'top',
					items: [
						this.sfFilter
					]
				} )
			]
		} );

		return [
			this.pnlIntro,
			this.gdWatchlist
		];
	},

	renderPagePrefixedText: function( value, meta, record ) {
		var link = record.get( 'page_link' );
		return link;
	},

	renderHasUnreadChanges: function( value, meta, record ) {
		var badge = '';
		if( record.get( 'has_unread_changes' ) ) {
			//HINT "Inspect all unread changes" URL looks like this:
			//https://wiki/index.php?title=XYZ&diff=0&oldid=108279
			var url = mw.util.getUrl( record.get( 'page_prefixedtext' ), {
				diff: 0,
				oldid: record.get( 'unread_changes_diff_revid' )
			} );
			badge = mw.html.element(
				'a',
				{
					'class': 'label label-warning bs-icon-eye inspect-changes-icon',
					'title': mw.message( 'bs-watchlist-grid-watchlist-column-hasunreadchanges-tooltip' ).plain(),
					'style': 'margin-left: 0.5em',
					'href': url,
					'target': '_blank'
				},
				' ' // must be space to keep '.label' visible
			);
		}
		return badge;
	},

	onActionRemoveClick:function( view, rowIndex, colIndex, item, e, record, row ) {
		var pageName = record.get( 'page_prefixedtext' );
		var me = this;

		mw.loader.using( 'mediawiki.api' ).done( function() {
			var api = new mw.Api();
			api.unwatch( pageName ).done( function() {
				me.strWatchlist.reload();
			} );
		} );
	},

	makeButtons: function() {
		this.callParent( arguments );
		return [
			this.btnCancel
		];
	},
} );