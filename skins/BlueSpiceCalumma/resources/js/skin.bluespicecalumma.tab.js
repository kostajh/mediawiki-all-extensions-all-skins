( function( d, $, mw ) {
	$( d ).on( 'shown.bs.tab', '.bs-tabs a[data-toggle="tab"]', function (e) {
		var $anchor = $(this);
		var targetId = $anchor.attr( 'href' ).substring( 1 ); //cut off leading '#'
		var $tabsContainer = $anchor.closest( '.bs-tabs' );
		var tabsContainerId = $tabsContainer.attr( 'id' );

		bs.calumma.cookie.set( 'CalummaTab_'+tabsContainerId, targetId );
	});
})( document, jQuery, mediaWiki );
