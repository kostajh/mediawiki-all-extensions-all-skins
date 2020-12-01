<?php

namespace MediaWiki\Extension\WikibaseManifest;

use ExtensionRegistry;
use MediaWiki\Extensions\OAuth\Frontend\SpecialPages\SpecialMWOAuthConsumerRegistration;
use MediaWiki\Special\SpecialPageFactory;
use PHPUnit\Framework\TestCase;

class OAuthUrlFactoryTest extends TestCase {

	public function testGetOAuthUrl_returnsNullOAuthIfExtensionMissing() {
		$extensionRegistry = $this->createMock( ExtensionRegistry::class );
		$specialPageFactory = $this->createMock( SpecialPageFactory::class );
		$OAuthUrlFactory = new OAuthUrlFactory( $extensionRegistry, $specialPageFactory );
		$this->assertInstanceOf( NullOAuthUrl::class, $OAuthUrlFactory->getOAuthUrl() );
	}

	public function testGetOAuthUrl_returnsSpecialPageOAuthIfPresent() {
		$extensionRegistry = $this->createMock( ExtensionRegistry::class );
		$specialPageFactory = $this->createMock( SpecialPageFactory::class );
		$mockSpecialPage = $this->createMock( SpecialMWOAuthConsumerRegistration::class );
		$specialPageFactory->expects( $this->atLeastOnce() )
			->method( 'getPage' )->willReturn( $mockSpecialPage );
		$extensionRegistry->expects( $this->atLeastOnce() )
			->method( 'isLoaded' )->willReturn( true );
		$OAuthUrlFactory = new OAuthUrlFactory( $extensionRegistry, $specialPageFactory );
		$this->assertInstanceOf( SpecialPageOAuthUrl::class, $OAuthUrlFactory->getOAuthUrl() );
	}
}
