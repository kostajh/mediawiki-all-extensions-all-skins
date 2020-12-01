<?php

namespace PasswordlessLogin\adapter;

use HTMLFormField;
use MediaWiki\MediaWikiServices;
use OOUI\HtmlSnippet;
use OOUI\Widget;

/**
 * Renders a Google Play link badge for the Passwordless Login app
 */
class HTMLPlayStoreField extends HTMLFormField {
	/**
	 * @inheritDoc
	 */
	public function __construct( $info ) {
		$info['nodata'] = true;
		parent::__construct( $info );
	}

	/**
	 * @inheritDoc
	 */
	public function getInputHTML( $value ) {
		$assets = MediaWikiServices::getInstance()->getMainConfig()->get( 'ExtensionAssetsPath' );
		$badgeUrl = htmlspecialchars( $assets . '/PasswordlessLogin/ui/google-play-badge.png' );

		return '
<a target="_blank"href="https://play.google.com/store/apps/details?id=org.droidwiki.passwordless">
	<img height="60px" src="' . $badgeUrl . '" alt="Google Play" />
</a>
<div style="font-size:10px;color:grey;">
	Google Play and the Google Play logo are trademarks of Google LLC.
</div>';
	}

	/**
	 * @inheritDoc
	 */
	public function getInputOOUI( $value ) {
		return new Widget( [
			'content' => new HtmlSnippet( $this->getInputHTML( $value ) ),
		] );
	}

	/**
	 * @inheritDoc
	 */
	protected function needsLabel() {
		return false;
	}

	private function getSource() {
		if ( $this->source ) {
			return $this->source;
		}

		return $this->dataUri;
	}
}
