Ext.define( 'BS.Bookshelf.tree.NumberedNodeColumn', {
	extend: 'Ext.tree.Column',
	width: Ext.isIE6 ? '100%' : 10000, //Taken from Ext.tree.Panel source http://docs.sencha.com/extjs/4.2.2/source/Panel.html#Ext-tree-Panel

	parentTreePanel: null,

	initComponent: function() {
		//This is necessary to have the whole treepanel be re-rendered after
		//the tree has changed. Otherwise ony rows 'above' will be re-rendered
		if( this.parentTreePanel ) {
			this.parentTreePanel.on( 'itemmove', this.onItemMove, this );
			//This is necessary to have a horizontal scroll bar appear when the
			//treenode's line overflows the panel's width
			this.parentTreePanel.addCls( Ext.baseCSSPrefix + 'autowidth-table' );
		}
		this.scope = this; //To have the correct scope set within the 'renderer' method
		this.callParent();
	},

	renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
		var bs = record.get('bookshelf');

		if(record.parentNode !== null) {
			// In some cases some nodes could be too far from their parent, so we need to heal the tree
			if(record.getDepth() - record.parentNode.getDepth() > 1) {
				record.updateInfo(true, {depth: record.parentNode.getDepth() + 1});
			}
		}

		bs.number = this.calcNumber(record);
		record.set('bookshelf', bs);
		var number = mw.html.element(
			'span',
			{
				'class': 'bs-chapter-number'
			},
			bs.number
		);
		var text = mw.html.element(
			'span',
			{
				'class': 'bs-chapter-title'
			},
			new mw.html.Raw( record.get( 'articleDisplayTitle' ) )
		);

		var classes = [
			'bs-chapter-depth-' + record.getDepth(),
			'bs-chapter-type-' + bs.type
		];

		if( bs.page_id === 0 ) {
			classes.push( 'bs-chapter-new' );
		}

		var output = mw.html.element(
			'span', {
				'class': classes.join( ' ' )
			},
			new mw.html.Raw( number + text )
		);

		return output;
	},

	calcNumber: function( record ) { //'record' is a NodeInterface
		var currentNode = record;
		var nodePosition = 1;
		var prefix = '';

		while( currentNode ) {
			if(!currentNode.previousSibling) {
				if( currentNode.parentNode ) {
					prefix = nodePosition + '.' + prefix;
					nodePosition = 0;
					currentNode = currentNode.parentNode;
				}
				else {
					//currentNode.set( 'articleNumber', prefix );
					return prefix + ' ';
				}
			}
			else {
				currentNode = currentNode.previousSibling;
			}
			nodePosition++;
		}

		return prefix;
	},

	onItemMove: function( node, oldParent, newParent, index, eOpts ) {
		this.parentTreePanel.getView().refresh();
	}
});
