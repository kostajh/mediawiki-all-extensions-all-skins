<?php

namespace Wikibase\Lib\Tests\Store;

use Http;
use PHPUnit\Framework\Assert;

/**
 * Http mock for the HttpUrlPropertyOrderProviderTest.
 *
 * @private
 * @see Http
 *
 * @license GPL-2.0-or-later
 * @author Marius Hoch
 */
class HttpUrlPropertyOrderProviderTestMockHttp extends Http {

	/**
	 * @var mixed
	 */
	public static $response;

	public static function get( $url, array $options = [], $caller = __METHOD__ ) {
		Assert::assertSame( 'page-url', $url );
		Assert::assertIsArray( $options );
		Assert::assertIsString( $caller );

		return self::$response;
	}

}
