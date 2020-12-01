Ext.define('BS.Bookshelf.TreeHelper', {
	statics: {
		//http://forums.ext.net/showthread.php?2479-SOLVED-should-remove-for-node-in-tree-panel
		cloneTreeNode: function(oNode) {
			var oAttributes = oNode.attributes;
			oAttributes.id = Ext.id(); //Create a new unique id for the object withi the DOM
			var oNodeClone = new Ext.tree.TreeNode(Ext.apply({}, oAttributes));
			oNodeClone.text = oNode.text;

			for (var i = 0; i < oNode.childNodes.length; i++) {
				var oCurrentNode = oNode.childNodes[i];
				var oCurrentNodeClone = this.cloneTreeNode(oCurrentNode);
				oNodeClone.appendChild(oCurrentNodeClone);
			}
			return oNodeClone;
		},
		/* Ext.encode() invokes too much recursion, because every TreeNode has
		 * references to its tree, parentnode, siblings and so on. Serialization
		 * has to be done manually.
		 */
		serializeTree: function(oTree, oOptions) {
			var oDefaultOptions = {
				onlyChecked: false,
				returnEncoded: true
			};
			if (oOptions) {
				oDefaultOptions = Ext.apply(oDefaultOptions, oOptions);
			}
			var arSerializedTree = [];
			var oRootNode = oTree.getRootNode();

			for (var i = 0; i < oRootNode.childNodes.length; i++) {
				this.serializeTreeNode(oRootNode.childNodes[i], arSerializedTree, oDefaultOptions);
			}

			// TODO RBV (28.01.12 10:54): Change when possible!
			if (oDefaultOptions.returnEncoded) {
				return Ext.encode(arSerializedTree);
			}
			else {
				return arSerializedTree;
			}
		},
		serializeTreeNode: function(oNode, arSerializedTree, oOptions) {
			var bs = oNode.get('bookshelf');

			var arEntry = {
				'number': bs.number,
				'title': oNode.get('articleTitle'),
				'display-title': oNode.get('text'),
				'bookshelf': bs
			};

			if (!oOptions.onlyChecked || oNode.get('checked')) {
				arSerializedTree.push(arEntry);
			}

			if (oNode.hasChildNodes()) {
				var bIsExpanded = oNode.isExpanded();

				if (bIsExpanded === false) {
					oNode.expand();
				} // if not -> oNode.childNodes.length always is 0

				for (var j = 0; j < oNode.childNodes.length; j++) {
					this.serializeTreeNode(oNode.childNodes[j], arSerializedTree, oOptions);
				}

				if (bIsExpanded === false) {
					oNode.collapse();
				} //restore expand-state
			}
		}
	}
});
