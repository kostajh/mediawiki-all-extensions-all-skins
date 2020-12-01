(function( mw, $, bs, d, undefined ){
	$(d).on('BSContextMenuBeforeCreate', function( e, $anchor, items ) {
		var bsTitle = $anchor.data('bs-title');
		if( !bsTitle ) {
		        return;
		}
		var title = new mw.Title( bsTitle );
		if( title.getNamespaceId() === bs.ns.NS_BOOK ) {
			items.push({
				iconCls: 'icon-book2',
				text: mw.message('bs-bookshelfui-widget-editor-link-text').plain(),
				href: mw.util.getUrl(
					'Special:BookshelfBookEditor/' + title.getPrefixedText()
				)
			});
		}
	});
})( mediaWiki, jQuery, blueSpice, document );
