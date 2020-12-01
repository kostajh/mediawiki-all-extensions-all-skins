/**
 * Backport of https://github.com/wikimedia/mediawiki-extensions-VisualEditor/commit/f8cebb8f5156b1fb012f6532ad950e90b4a89da6
 */
mw.hook( 've.activationComplete' ).add( function () {

	/**
	 * Set temporary redirect interface to match the current state of redirection in the editor.
	 *
	 * @param {string|null} title Current redirect target, or null if none
	 */
	ve.init.mw.DesktopArticleTarget.prototype.setFakeRedirectInterface = function ( title ) {
		this.updateRedirectInterface(
			title ? this.constructor.static.buildRedirectSub() : $(),
			title ? this.constructor.static.buildRedirectMsg( title ) : $()
		);
	};

	/**
	 * Set the redirect interface to match the page's redirect state.
	 */
	ve.init.mw.DesktopArticleTarget.prototype.setRealRedirectInterface = function () {
		this.updateRedirectInterface(
			mw.config.get('wgIsRedirect') ? this.constructor.static.buildRedirectSub() : $(),
			// Remove our custom content header - the original one in #mw-content-text will be shown
			$()
		);
	};

} );
