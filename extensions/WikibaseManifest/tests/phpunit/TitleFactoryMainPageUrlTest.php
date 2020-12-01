<?php

namespace WikibaseManifest\Test;

use MediaWiki\Extension\WikibaseManifest\TitleFactoryMainPageUrl;
use PHPUnit\Framework\TestCase;
use Title;
use TitleFactory;

/**
 * @covers \MediaWiki\Extension\WikibaseManifest\TitleFactoryMainPageUrl
 */
class TitleFactoryMainPageUrlTest extends TestCase {

	public function test() {
		$mockTitle = $this->createMock( Title::class );
		$mockTitle->expects( $this->once() )
			->method( 'getFullUrl' )
			->willReturn( 'http://example.com/MainPage' );

		$mockTitleFactory = $this->createMock( TitleFactory::class );
		$mockTitleFactory->expects( $this->once() )
			->method( 'newMainPage' )
			->willReturn( $mockTitle );

		$mainPageUrl = new TitleFactoryMainPageUrl( $mockTitleFactory );
		$value = $mainPageUrl->getValue();
		$this->assertEquals( 'http://example.com/MainPage', $value );
	}

}
