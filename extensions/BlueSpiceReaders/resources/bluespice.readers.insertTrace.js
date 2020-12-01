( function( mw, $, bs, undefined ) {
	var data = {
		revisionId: mw.config.get( 'wgRevisionId' )
	};
	mw.hook( 'readers.check.revision.before.insertTrace' ).fire( data );
	bs.api.tasks.execSilent(
		'readers',
		'insertTrace',
		{ revId: data.revisionId }
	);
} )( mediaWiki, jQuery, blueSpice );
