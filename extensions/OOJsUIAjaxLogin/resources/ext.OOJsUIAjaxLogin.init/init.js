( function ( $ ) {
	/**
	 * Loginlink click handler.
	 * Opens the login OOJs dialog or redirect to the login page, if an error occurs.
	 *
	 * @param {jQuery.Event} ev
	 * @ignore
	 */
	function onLoginClick( ev ) {
		var $el = $( ev.target ),
			$oldEl = $el;

		// replace login link with a loading text to indicate, that the login form is loading
		$el.replaceWith(
			mw.template.get( 'ext.OOJsUIAjaxLogin.init', 'loginLink.mustache' ).render( {
				linkMsg: mw.msg( 'oojsuiajaxlogin-loading' )
			} )
		);

		// load the overlay module and show the overlay
		mw.loader.using( 'ext.OOJsUIAjaxLogin.overlay', function () {
			// replace the loading text with the old login link
			$( '#oojsui-ajaxlogin' ).replaceWith( $oldEl );
			// re-add event listener
			$oldEl.on( 'click', onLoginClick );
			mw.OOJsUIAjaxLogin.show();
		}, function () {
			// if the module could not be loaded, navigate to the original target
			location.href = ev.target.href;
		} );
		// don't navigate to Special:UserLogin
		ev.preventDefault();
	}

	// add click event listener to login link in personal tools
	$( '#pt-login a' ).on( 'click', onLoginClick );
}( jQuery ) );
