$( '.bs-subpagetree' ).each( function() {
	Ext.create('BS.tree.WikiSubPages', {
		renderTo: this,//this div is created by BlueSpiceSubPageTree::renderPageTree (ParserFirstCallInit)
		treeRootPath: $(this).data( 'root' ),
		listeners: {
			itemclick: function( obj, record, item, index, e, eOpts ){
				window.location.href = mw.util.wikiGetlink( record.internalId ) ;
			}
		}
	});
});