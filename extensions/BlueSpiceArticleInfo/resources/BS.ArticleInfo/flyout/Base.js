Ext.define( 'BS.ArticleInfo.flyout.Base', {
	extend: 'BS.flyout.TwoColumnsBase',
	requires: ['BS.ArticleInfo.panel.LastEditedTime', 'BS.ArticleInfo.panel.LastEditedUser'],

	makeItemCallbacks: [],
	basicData: {},
	lastEditedTime: {},
	lastEditedUser: {},
	pageCategoryLinks: {},
	templateLinks: {},
	initComponent: function() {
		this.allItems = this.makeItems();
		for( let i = 0; i < this.makeItemCallbacks.length; i++ ) {
			var callback = this.makeItemCallbacks[i];
			var response = bs.util.runCallback( callback, [ this, this.basicData ], this );
			if ( response && typeof response.then === 'function' ) {
				response.done( function( newItems ) {
					this.addNewItems( newItems );
				}.bind( this ) )
			} else {
				this.addNewItems( response );
			}
		}

		this.callParent(arguments);
	},
	addNewItems: function( newItems ) {
		var me = this;
		$.each( newItems, function( key, items ) {
			if( me.allItems[key] ) {
				me.allItems[key] = $.merge( me.allItems[key], items );
			} else {
				me.allItems[key] = items;
			}
		} );
	},
	makeCenterTwoItems: function() {
		var items = this.allItems.centerRight || [];
		if( this.hasSubpages ) {
			var root = mw.config.get( 'wgPageName' );
			if( mw.config.get( 'wgNamespaceNumber' ) === bs.ns.NS_MAIN ) {
				root = ':' + root;
			}

			items.unshift(
				Ext.create( 'BS.tree.WikiSubPages', {
					treeRootPath: root,
					renderLinks: true,
					maxHeight: 300,
					title: mw.message( 'bs-articleinfo-flyout-subpages-title' ).plain(),
					cls: 'bs-articleinfo-flyout-templatelist-cnt'
				} )
			);
		}

		return items;
	},

	makeCenterOneItems: function() {
		return this.allItems.centerLeft || [];
	},

	makeTopPanelItems: function() {
		return this.allItems.top || [];
	},

	makeBottomPanelItems: function() {
		return this.allItems.bottom || [];
	},

	makeItems: function() {
		var topItems = [];

		if( $.isEmptyObject( this.lastEditedTime ) === false ) {
			topItems.push(
				Ext.create( 'BS.ArticleInfo.panel.LastEditedTime', {
					timestampText: this.lastEditedTime.timestamp,
					anchorURL: this.lastEditedTime.url
				} )
			);
		}
		if( $.isEmptyObject( this.lastEditedUser ) === false ) {
			topItems.push(
				Ext.create( 'BS.ArticleInfo.panel.LastEditedUser', {
					userText: this.lastEditedUser.userText,
					anchorURL: this.lastEditedUser.url
				} )
			);
		}

		var leftItems = [
			Ext.create( 'BS.ArticleInfo.panel.LinkList', {
				linkList: this.pageCategoryLinks,
				storeField: 'category_anchor',
				title: mw.message( 'bs-articleinfo-flyout-categorylinks-title' ).plain(),
				emptyText: mw.message( 'bs-articleinfo-flyout-categorylinks-emptytext' ).plain(),
				cls: 'bs-articleinfo-flyout-categorylist-cnt',
				listType: 'pills'
			} ),
			Ext.create( 'BS.ArticleInfo.panel.LinkList', {
				linkList: this.templateLinks,
				storeField: 'template_anchor',
				title: mw.message( 'bs-articleinfo-flyout-templatelinks-title' ).plain(),
				emptyText: mw.message( 'bs-articleinfo-flyout-templatelinks-emptytext' ).plain(),
				cls: 'bs-articleinfo-flyout-templatelist-cnt',
				listType: 'pills'
			} )
		];

		return {
			top: topItems,
			centerLeft: leftItems
		};
	}
} );
