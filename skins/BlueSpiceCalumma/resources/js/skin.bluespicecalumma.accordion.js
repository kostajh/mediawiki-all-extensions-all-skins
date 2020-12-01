$( function( mw, $, d ) {
	$( d ).on( 'shown.bs.collapse', '.bs-accordion', function (e) {
		var $panel = $(e.target).closest( '.bs-accordion-panel' );
		var targetId = $panel.attr( 'id' );
		var tabsContainerId = $(this).attr( 'id' );

		bs.calumma.cookie.set( 'CalummaAccordion_'+tabsContainerId, targetId );
	});
})( mediaWiki, jQuery, document );
