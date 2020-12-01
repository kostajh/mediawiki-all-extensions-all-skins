$( function () {

	var $references = $( '#mw-content-text .references', document.body ),
		$cites = $( '#mw-content-text .reference', document.body );

	if ( $references.length && $cites.length ) {
		// eslint-disable-next-line no-new
		new mw.Citedrawer( $cites, $references );
	}

} );
