( function( d, $, mw ) {
	$( d ).on( 'click', '#pageinfo-expiry-expired', function( e ){
		e.preventDefault();

		$( '#bs-toolpanetabs ul.nav.nav-pills li.active' ).removeClass( 'active' );
		$( '#bs-toolpanetabs ul.nav.nav-pills li' ).each( function( index, element ){
			var tab = $( element ).children( 'a' ).first();
			if ( $( tab ).attr('href') === '#bs-qualitymanagement-panel' ) {
				$( element ).addClass( 'active' );
			}
		});

		$( '#bs-toolpanetabs div.tab-content div.tab-pane.active' ).removeClass( 'active' );
		$( '#bs-toolpanetabs div.tab-content div.tab-pane' ).each( function( index, element ){
				$( '#bs-qualitymanagement-panel' ).addClass( 'active' );
		});

		if ( $( 'body' ).hasClass( 'sitetools-main-collapse' ) ) {
			$( 'body' ).removeClass( 'sitetools-main-collapse' );
		}

		var trigger = $( '#bs-expiry-flyout a.title' );
		if ( $( trigger).attr('href') === '#pnl-bs-expiry-flyout' ) {
			$( trigger ).trigger( 'click' );
		}

	});
})( document, jQuery, mediaWiki );