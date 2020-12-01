( function( mw, $ ) {
	var $logoAnchor, anchorSelectors = [
		//MediaWiki Skin
		'#p-logo a',
		//Maybe BlueSpice Skin
		'#bs-logo a',
		//BlueSpiceCalumma
		'nav.navbar.calumma-desktop-visible .bs-logo a',
		//BlueSpiceCalumma mobile
		'nav.navbar.calumma-mobile-visible .bs-logo a',
		//Okay, now we're desperate
		'a[title="' + mw.message( 'tooltip-p-logo' ).plain() + '"]'
	];
	for( var i = 0; i < anchorSelectors.length; i++ ) {
		$logoAnchor = $( anchorSelectors[i] ).first();
		if ( !$logoAnchor || $logoAnchor.length < 1 ) {
			continue;
		}
		$logoAnchor.attr(
			'href',
			mw.util.getUrl( 'Special:UserDashboard' )
		);
	}
} )( mediaWiki, jQuery );