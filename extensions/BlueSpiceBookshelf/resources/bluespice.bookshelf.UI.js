(function( mw, $, bs, undefined ) {
	var $container = $( '#bs-bookshelfui-book-toc' );
	if( $container.length === 0 ) {
		return;
	}
	var containerHeight = $container.height();
	var $activeNode = $( '#bs-bookshelfui-book-toc .bs-treenode-value .active' );
	if( $activeNode.length === 0 ) {
		return;
	}

	var $bookLink = $( '.bs-tab-link-bs-bookshelfui' );
	$bookLink.addClass( 'bs-booktab-in-book' );
	$( '#bs-nav-sections' ).tabs(
		'option',
		'active',
		$bookLink.parent().index() //this is bad...
	);

	$container.scrollTop(
		$activeNode.offset().top
		- $container.offset().top
		- containerHeight / 3
	);

})( mediaWiki, jQuery, blueSpice );