(function( mw, $, bs, undefined ){
	if ( mw.config.get( "wgCurRevisionId" ) < 1 ) {
		return;
	}

	var checkRevisionInterval =
		mw.config.get( 'bsgArticleInfoCheckRevisionInterval' ) * 1000;

	if( checkRevisionInterval < 1000 ) {
		return;
	}

	BSPing.registerListener(
		'ArticleInfo',
		checkRevisionInterval,
		[ 'checkRevision', mw.config.get( "wgAction" ) ],
		_checkRevisionListener
	);

	function _checkRevisionListener( result, Listener ) {
		if( result.success !== true ) {
			return;
		}
		if( result.newRevision !== true ) {
			BSPing.registerListener(
				'ArticleInfo',
				checkRevisionInterval,
				[ 'checkRevision', mw.config.get( "wgAction" ) ],
				_checkRevisionListener
			);
			return;
		}

		var $elem = $('<div>').append( result.checkRevisionView );

		bs.alerts.add(
			'bs-articleinfo-newrevision-info',
			$elem,
			bs.alerts.TYPE_INFO
		);
	}
})( mediaWiki, jQuery, blueSpice );