<?php

namespace WikibaseManifest\Test;

use ExtensionRegistry;
use MediaWiki\Extension\WikibaseManifest\SpecialPageOAuthUrl;
use MediaWiki\Extensions\OAuth\Frontend\SpecialPages\SpecialMWOAuthConsumerRegistration;
use MediaWikiIntegrationTestCase;
use WikiMap;

class SpecialPageOAuthUrlTest extends MediaWikiIntegrationTestCase {

	public function testGetValue() {
		if ( !ExtensionRegistry::getInstance()->isLoaded(
			'OAuth' ) ) {
			$this->markTestSkipped( 'OAuth is not enabled' );
		}
		$this->mockThisWikiToBeAnOAuthCentralWiki();
		$specialPage = new SpecialMWOAuthConsumerRegistration();
		$OAuthUrl = new SpecialPageOAuthUrl( $specialPage );
		$this->assertStringEndsWith( 'Special:OAuthConsumerRegistration', $OAuthUrl->getValue() );
	}

	private function mockThisWikiToBeAnOAuthCentralWiki(): void {
		$this->setMwGlobals(
			[
				'wgMWOAuthCentralWiki' => WikiMap::getCurrentWikiId(),
			]
		);
	}
}
