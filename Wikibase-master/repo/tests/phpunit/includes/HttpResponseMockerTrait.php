<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Tests;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @license GPL-2.0-or-later
 */
trait HttpResponseMockerTrait {

	/**
	 * Some test appear to want to simulate null status codes, hence the type hint
	 */
	private function newMockResponse( $response, ?int $statusCode ): ResponseInterface {
		$mockStream = $this->createMock( StreamInterface::class );
		$mockStream->expects( $this->any() )
			->method( 'getContents' )
			->willReturn( $response );

		$httpResponse = $this->createMock( ResponseInterface::class );
		$httpResponse->expects( $this->any() )
			->method( 'getStatusCode' )
			->willReturn( $statusCode );
		$httpResponse->expects( $this->any() )
			->method( 'getBody' )
			->willReturn( $mockStream );

		return $httpResponse;
	}

}
