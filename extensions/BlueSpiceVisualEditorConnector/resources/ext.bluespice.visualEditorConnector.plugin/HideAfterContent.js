// I tried hard with toolFactory registration, but
// eventually gave up. This is a hacky but working
// solution.
mw.hook( 've.activationComplete' ).add( function () {
	$( '.bs-data-after-content' ).hide();
});

mw.hook( 've.deactivationComplete' ).add( function () {
	$( '.bs-data-after-content' ).show();
});
