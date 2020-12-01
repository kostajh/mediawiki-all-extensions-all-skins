Ext.define( 'BS.Calumma.flyout.RecentChanges', {
	extend: 'BS.flyout.TabbedDataViewBase',
	requires: ['BS.store.ApiRecentChanges'],

	showAddIcon: function() {
		return false;
	},

	makeCommonStore: function() {
		return new BS.store.ApiRecentChanges();
	},

	makeDataViewThumbImageModuleName: function() {
		return 'articlepreviewimage';
	},

	makeDataViewItemLinkUrl: function( dataset ) {
		return dataset.oldid_url;
	},

	makeDataViewThumbImageTitletextValue: function( dataset ) {
		return dataset.page_prefixedtext;
	},

	makeDataViewThumbnailCaptionTitle: function( dataset ) {
		return dataset.page_prefixedtext;
	},

	makeDataViewItemMetaItems: function( dataset ) {
		if( dataset.user_display_name && dataset.user_display_name !== '' ) {
			var name = dataset.user_display_name;
		}
		else {
			var name = dataset.user_name;
		}
		return [
			{ itemHtml: '<span>' + dataset.user_name + '</span><span> (' + dataset.timestamp + ')</span>' },
			{ itemHtml: '<span>' + dataset.comment_text + '</span>' }
		];
	},

	dataViewItemHasMenu: function( dataset ) {
		return true;
	},

	makeToolsLinkText: function() {
		return '';
	},

	makeTooleMenu: function( dataset ) {
		var items = [ {
			plain: true,
			iconCls: 'bs-icon-history',
			text: mw.message( 'bs-calumma-recentchanges-history' ).plain(),
			onClick: function(){
				window.location = dataset.get( 'hist_url' );
			}
		} ];
		if ( dataset.get( 'diff_link' ) !== '' ) {
			items.push( {
				plain: true,
				iconCls: 'bs-icon-history',
				text: mw.message( 'bs-calumma-recentchanges-diff' ).plain(),
				onClick: function() {
					window.location = dataset.get( 'diff_url' );
				}
			} );
		}
		return new Ext.menu.Menu( {
			items: items
		} );
	},

	makeGridPanelColums: function() {
		return [{
			header: mw.message( 'bs-calumma-recentchanges-column-header-title' ).plain(),
			dataIndex: 'page_prefixedtext',
			flex: 1,
			filter: {
				type: 'string'
			},
			renderer: function( value, metadata, record ) {
				var diff = '';
				var history = '';

				if ( record.get( 'diff_url' ).length > 0 ) {
					diff = mw.html.element(
						'a',
						{
							'href': record.get( 'diff_url' )
						},
						mw.message( 'bs-calumma-recentchanges-diff' ).plain()
					);

					diff = diff + ' | ';
				}

				if ( record.get( 'hist_url' ) ) {
					history = mw.html.element(
						'a',
						{
							'href': record.get( 'hist_url' )
						},
						mw.message( 'bs-calumma-recentchanges-history' ).plain()
					);
				}

				return '<div><span class="title">' + record.get( 'oldid_link' ) + '</span><span class="actions">( ' + diff + history + ' )</span></div>';
			}
		}];
	},

	makeDataViewThumbImageRevidValue: function( values ) {
		return values.this_oldid;
	}
});
