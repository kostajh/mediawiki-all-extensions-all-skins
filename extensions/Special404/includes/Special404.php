<?php

class Special404 extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'Error404' );
	}

	/**
	 * @param string|null $par
	 */
	public function execute( $par ) {
		// phpcs:ignore MediaWiki.NamingConventions.ValidGlobalName.allowedPrefix
		global $wgOut, $wgRequest, $egSpecial404RedirectExistingRoots;

		if ( $egSpecial404RedirectExistingRoots ) {
			$titles = [
				$wgRequest->getRequestURL(),
				trim( $wgRequest->getRequestURL(), '/\\' ),
				urldecode( $wgRequest->getRequestURL() ),
				urldecode( trim( $wgRequest->getRequestURL(), '/\\' ) ),
			];
			foreach ( $titles as $pageTitle ) {
				$t = Title::newFromText( $pageTitle );
				if ( $t && $t->exists() ) {
					$wgOut->redirect( $t->getFullURL(), 301 );
					return;
				}
			}
		}

		$this->setHeaders();
		$wgOut->setStatusCode( 404 );
		$wgOut->addWikiMsg( 'special404-body', trim( $wgRequest->getRequestURL(), '/\\' ) );
	}

}
