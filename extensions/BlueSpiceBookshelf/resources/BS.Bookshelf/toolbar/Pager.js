Ext.define( 'BS.Bookshelf.toolbar.Pager', {
	extend: 'Ext.toolbar.Toolbar',

	bookTreePanel: null,
	flattenedTree: [],

	initComponent: function() {
		this.btnNextArticle = new Ext.Button({
			text     : mw.message('bs-bookshelf-tag-next').plain(),
			iconCls  : 'x-tbar-page-next',
			iconAlign: 'right',
			href: '#', //Needs to be set on init to have link functionality
			hrefTarget: '_self', //ExtJS default is '_blank' !?
			disabled : true
		});

		this.btnPrevArticle = new Ext.Button({
			text    : mw.message('bs-bookshelf-tag-prev').plain(),
			iconCls : 'x-tbar-page-prev',
			href: '#',
			hrefTarget: '_self',
			disabled: true
		});

		this.bookTreePanel.on( 'contextestablished', this.onContextEstablished, this );

		this.items = [
			this.btnPrevArticle,
			'->',
			this.btnNextArticle
		];

		this.flattenTree();

		this.callParent();
	},

	onContextEstablished: function( sender, oCurrentArticleNode ) {
		this.setCurrentArticleNode( oCurrentArticleNode );
	},

	setCurrentArticleNode: function( oCurrentArticleNode ) {
		this.btnNextArticle.disable();
		this.btnPrevArticle.disable();

		this.oNextArticleNode = this.findNextArticleNode( oCurrentArticleNode );
		this.oPrevArticleNode = this.findPrevArticleNode( oCurrentArticleNode );

		if( this.oNextArticleNode !== null ) {
			this.btnNextArticle.enable();
			this.btnNextArticle.setHref(
				mw.util.getUrl(
					this.oNextArticleNode.get('articleTitle')
				)
			);
		}
		if( this.oPrevArticleNode !== null ) {
			this.btnPrevArticle.enable();
			this.btnPrevArticle.setHref(
				mw.util.getUrl(
					this.oPrevArticleNode.get('articleTitle')
				)
			);
		}
	},

	flattenTree: function() {
		var me = this;
		this.bookTreePanel.getRootNode().cascadeBy( function( node ){
			me.flattenedTree.push( node );
		});
	},

	//TODO: Make a flat list out of the tree. It is then much easier to calculate next and previous
	findNextArticleNode: function( oCurrentNode ) {
		if( oCurrentNode === null ) {
			return null;
		}

		for( var i = 0; i <= this.flattenedTree.length; i++ ) {
			if( this.flattenedTree[i] === oCurrentNode ) {
				return this.flattenedTree[i + 1] || null;
			}
		}
		return null;
	},

	findPrevArticleNode: function( oCurrentNode ) {
		for( var i = 0; i <= this.flattenedTree.length; i++ ) {
			if( this.flattenedTree[i] === oCurrentNode ) {
				return this.flattenedTree[i - 1] || null;
			}
		}
		return null;
	}
});