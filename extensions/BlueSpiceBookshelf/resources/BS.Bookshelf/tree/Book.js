Ext.define( 'BS.Bookshelf.tree.Book', {
	extend: 'Ext.tree.Panel',
	requires: [ 'BS.Bookshelf.model.NumberedPageNode',
		'BS.Bookshelf.tree.NumberedNodeColumn' ],

	//HINT: http://stackoverflow.com/questions/17548018/extjs-treepanel-store-load-problems
	animCollapse: false,
	animate: false,
	autoScroll : false,
	rootVisible: false,
	useArrows  : true,
	hideHeaders: true,

	treeData: {},
	currentArticleId: -1,
	autoTitle: true,

	initComponent: function() {
		this.store =  new Ext.data.TreeStore({
			root: this.treeData,
			model: 'BS.Bookshelf.model.NumberedPageNode'
		});
		this.columns = [
			new BS.Bookshelf.tree.NumberedNodeColumn({
				parentTreePanel: this
			})
		];

		this.on( 'afterrender', this.dataLoaded, this );
		this.on( 'afterlayout', this.tpChaptersAfterlayout, this );
		this.on( 'afteritemexpand', this.tpChaptersAfteritemexpand, this );
		//this.on( 'itemclick', this.onItemclick, this );
		//this.on( 'load', this.onLoad, this );

		this.callParent(arguments);
	},

	dataLoaded: function( oSender ) {
		var me = this;
		this.oCurrentArticleNode = this.findChildRecursively(
			this.getRootNode().childNodes,
			this.currentArticleId
		);
		if ( this.oCurrentArticleNode ) {
			this.expandAll(function(){
				me.collapseAll(function(){
					me.selectPath( me.oCurrentArticleNode.getPath(), false, '/', function() {
						if( me.oCurrentArticleNode.hasChildNodes() ) {
							me.oCurrentArticleNode.expand();
						}
						this.fireEvent( 'contextestablished', me, me.oCurrentArticleNode );
					});
				});
			});
		}
		else {
			this.getRootNode().expand();
			this.oCurrentArticleNode = this.getRootNode();
			this.fireEvent( 'contextestablished', this, this.oCurrentArticleNode );
		}
		if( this.autoTitle ) {
			this.setTitle( this.getRootNode().get('text') );
		}

		//this is to have the browser scroll to the correct position if a
		//url fragment is set
		if(window.location.hash) {
			var hash = window.location.hash;
			window.location.hash = '';
			setTimeout('window.location.hash = "'+hash+'"', 1000);
		}
	},

	//HINT: http://www.sencha.com/forum/showthread.php?188131-Treepanel-scroll-a-node-into-view&p=949888&viewfull=1#post949888
	tpChaptersAfterlayout: function(container, layout, eOpts) {
		this.getView().focusRow( this.expandedIndex +1 );
	},

	expandedIndex: false,
	tpChaptersAfteritemexpand: function( node, index, item, eOpts ) {
		this.expandedIndex = index;
	},

	//Based on Allan's Code: http://www.extjs.com/forum/showthread.php?t=27178
	findChildRecursively: function( arNodes, iArticleId ) {
		for( var i = 0; i < arNodes.length ; i++ ) {
			//To load all childnodes
			var oThisNode = arNodes[i];
			oThisNode.expand();
			if( oThisNode.get('articleId') === iArticleId ) {
				return oThisNode;
			}
			else {
				if ( oThisNode.hasChildNodes() ) {
					var oNode = this.findChildRecursively( oThisNode.childNodes, iArticleId );
					if ( oNode ) {
						return oNode;
					}
				}
			}
			oThisNode.collapse();
		}
		return null;
	}
});
