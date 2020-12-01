( function( d, $, bs, mw ) {

	$( d ).on( 'click', '#ca-purge', function( e ){

		e.preventDefault();

		new mw.Api().post( {
				action: "purge",
				titles: mw.config.get('wgPageName'),
				format: "json"
			} )
			.fail(function( data ){
				mw.loader.using( "ext.bluespice.extjs" ).done(function(){
						bs.util.alert('bs-calumma-purge-failure', {
							text: mw.message('bs-calumma-purge-failed').plain()
						});
					});
			})
			.done(function( data ){
				bs.deferredNotifications.push( mw.message('bs-calumma-purged-successfully').plain() );
				location.reload();
			});

	});

})( document, jQuery, blueSpice, mediaWiki );