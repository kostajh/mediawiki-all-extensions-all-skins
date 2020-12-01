<?php

namespace MediaWiki\Wikispeech\Tests\Unit;

use InvalidArgumentException;
use MediaWiki\WikispeechSpeechDataCollector\UUID;
use MediaWikiUnitTestCase;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Unit
 * @covers \MediaWiki\WikispeechSpeechDataCollector\UUID
 * @since 0.1.0
 */
class UUIDTest extends MediaWikiUnitTestCase {

	//
	// isBytes and isHex
	//

	/**
	 * @dataProvider provideTestIsHex_isHex
	 * @param string $uuid
	 */
	public function testIsHex_isHex(
		string $uuid
	) {
		$this->assertTrue( UUID::isHex( $uuid ) );
	}

	public function provideTestIsHex_isHex() {
		return [
			'validHexWithoutDashes_isHex' => [ '20354d7ae4fe47af8ff6187bca92f3f9' ],
			'validHexWithDashes_isHex' => [ '20354d7a-e4fe-47af-8ff6-187bca92f3f9' ],
			'generatedWithoutDashes_isHex' => [ UUID::v4HexFactory() ],
			'generatedWithDashes_isHex' => [ UUID::v4HexFactory( true ) ],
		];
	}

	/**
	 * @dataProvider provideTestIsHex_isNotHex
	 * @param string $uuid
	 */
	public function testIsHex_isNotHex(
		string $uuid
	) {
		$this->assertFalse( UUID::isHex( $uuid ) );
	}

	public function provideTestIsHex_isNotHex() {
		return [
			'validHexWithDashesInWrongPlace_isNotHex' => [ '20354d-7ae4fe-47af-8ff6-187bca92f3f9' ],
			'validHexNotUuidTooFewBytes_isNotHex' => [ 'cafe' ],
			'validHexNotUuidTooManyBytes_isNotHex' => [ 'cafe01020304050607080910111213141516' ],
			'invalidHexNotUuid_isNotHex' => [ 'räksmörgås' ],
			'validBinaryUuid_isNotHex' => [ hex2bin( '20354d7ae4fe47af8ff6187bca92f3f9' ) ],
			'validBinaryNotUUID_isNotHex' => [ hex2bin( '1234567890123456' ) ],
			'validHexUuidV3_isNotHex' => [ 'a5764857-ae35-34dc-8f25-a9c9e73aa898' ],
			'validHexUuidV5_isNotHex' => [ 'b79cb3ba-745e-5d9a-8903-4a02327a7e09' ]
		];
	}

	/**
	 * @dataProvider provideTestIsBytes_isBytes
	 * @param string $uuid
	 */
	public function testIsBytes_isBytes(
		string $uuid
	) {
		$this->assertTrue( UUID::isBytes( $uuid ) );
	}

	public function provideTestIsBytes_isBytes() {
		return [
			'validBinaryUuid_isBytes' => [ hex2bin( '20354d7ae4fe47af8ff6187bca92f3f9' ) ],
			'generated_isBytes' => [ UUID::v4BytesFactory() ]
		];
	}

	/**
	 * @dataProvider provideTestIsBytes_isNotBytes
	 * @param string $uuid
	 */
	public function testIsBytes_isNotBytes(
		string $uuid
	) {
		$this->assertFalse( UUID::isBytes( $uuid ) );
	}

	public function provideTestIsBytes_isNotBytes() {
		return [
			'invalidBinaryUuidTooFewBytes_isNotBytes' =>
				[ hex2bin( '010203040506070809101112131415' ) ],
			'invalidBinaryUuidTooManyBytes_isNotBytes' =>
				[ hex2bin( '0102030405060708091011121314151617' ) ],
			'validHexUuidWithoutDashes_isNotBytes' => [ '20354d7ae4fe47af8ff6187bca92f3f9' ],
			'validHexUuidWithDashes_isNotBytes' => [ '20354d7a-e4fe-47af-8ff6-187bca92f3f9' ],
			'validBinaryUuidV3_isNotBytes' => [ hex2bin( 'a5764857ae3534dc8f25a9c9e73aa898' ) ],
			'validBinaryUuidV5_isNotBytes' => [ hex2bin( 'b79cb3ba745e5d9a89034a02327a7e09' ) ]
		];
	}

	//
	// asBytes and asHex
	//

	/**
	 * @dataProvider provideTestAsBytes_isSame
	 * @param string|null $expectedUuid
	 * @param string|null $testedUuid
	 */
	public function testAsBytes_isSame( ?string $expectedUuid, ?string $testedUuid ) {
		$this->assertSame(
			$expectedUuid,
			UUID::asBytes( $testedUuid )
		);
	}

	public function provideTestAsBytes_isSame(): array {
		$validV4BytesUuid = hex2bin( '20354d7ae4fe47af8ff6187bca92f3f9' );
		return [
			'null' => [ null, null ],
			'bytes' => [ $validV4BytesUuid, $validV4BytesUuid ],
			'hexWithDashes' => [ $validV4BytesUuid, '20354d7a-e4fe-47af-8ff6-187bca92f3f9' ],
			'hexWithoutDashes' => [ $validV4BytesUuid, '20354d7ae4fe47af8ff6187bca92f3f9' ]
		];
	}

	/**
	 * @dataProvider provideTestAsHexWithDashes_isSame
	 * @param string|null $expectedUuid
	 * @param string|null $testedUuid
	 */
	public function testAsHexWithDashes_isSame( ?string $expectedUuid, ?string $testedUuid ) {
		$this->assertSame(
			$expectedUuid,
			UUID::asHex( $testedUuid, true )
		);
	}

	public function provideTestAsHexWithDashes_isSame(): array {
		$validV4HexUuid = '20354d7a-e4fe-47af-8ff6-187bca92f3f9';
		return [
			'null' => [ null, null ],
			'bytes' => [ $validV4HexUuid, hex2bin( '20354d7ae4fe47af8ff6187bca92f3f9' ) ],
			'hexWithDashes' => [ $validV4HexUuid, $validV4HexUuid ],
			'hexWithoutDashes' => [ $validV4HexUuid, '20354d7ae4fe47af8ff6187bca92f3f9' ]
		];
	}

	/**
	 * @dataProvider provideTestAsHexWithoutDashes_isSame
	 * @param string|null $expectedUuid
	 * @param string|null $testedUuid
	 * @param bool $expected
	 */
	public function testAsHexWithoutDashes_isSame( ?string $expectedUuid, ?string $testedUuid ) {
		$this->assertSame(
			$expectedUuid,
			UUID::asHex( $testedUuid, false )
		);
	}

	public function provideTestAsHexWithoutDashes_isSame(): array {
		$validV4HexUuid = '20354d7ae4fe47af8ff6187bca92f3f9';
		return [
			'null' => [ null, null ],
			'bytes' => [ $validV4HexUuid, hex2bin( '20354d7ae4fe47af8ff6187bca92f3f9' ) ],
			'hexWithDashes' => [ $validV4HexUuid, '20354d7a-e4fe-47af-8ff6-187bca92f3f9' ],
			'hexWithoutDashes' => [ $validV4HexUuid, $validV4HexUuid ]
		];
	}

	/**
	 * @dataProvider provideTestAsHexAsBytes_invalid
	 * @param string|null $testedUuid
	 */
	public function testAsBytes_invalid( ?string $testedUuid ) {
		$this->expectException( InvalidArgumentException::class );
		UUID::asBytes( $testedUuid );
	}

	/**
	 * @dataProvider provideTestAsHexAsBytes_invalid
	 * @param string|null $testedUuid
	 */
	public function testAsHexWithDashes_invalid( ?string $testedUuid ) {
		$this->expectException( InvalidArgumentException::class );
		UUID::asHex( $testedUuid, true );
	}

	/**
	 * @dataProvider provideTestAsHexAsBytes_invalid
	 * @param string|null $testedUuid
	 */
	public function testAsHexWithoutDashes_invalid( ?string $testedUuid ) {
		$this->expectException( InvalidArgumentException::class );
		UUID::asHex( $testedUuid, false );
	}

	public function provideTestAsHexAsBytes_invalid(): array {
		return [
			'hexTooShort' => [ '010203040506070809101112131415' ],
			'hexTooLong' => [ '0102030405060708091011121314151617' ],
			'hexWithBadDashes' => [ '20-354d7ae4fe47af8ff6187bca-92f3f9' ],
			'hexValidV1WithDashes' => [ 'e5a8e630-203e-11eb-a5a3-f701e3625382' ],
			'hexValidV3WithDashes' => [ 'dcfcdfd9-7007-3a4c-862f-38a98dec4fac' ],
			'hexValidV5WithDashes' => [ 'a08d1615-8ad8-5047-bbf8-c607c62bd51a' ],
			'hexValidV1WithoutDashes' => [ 'e5a8e630203e11eba5a3f701e3625382' ],
			'hexValidV3WithoutDashes' => [ 'dcfcdfd970073a4c862f38a98dec4fac' ],
			'hexValidV5WithoutDashes' => [ 'a08d16158ad85047bbf8c607c62bd51a' ],
			'bytesToShort' => [ hex2bin( '01020304050607080910111213141516' ) ],
			'bytesToLong' => [ hex2bin( '0102030405060708091011121314151617' ) ],
			'bytesValidV1' => [ hex2bin( 'e5a8e630203e11eba5a3f701e3625382' ) ],
			'bytesValidV3' => [ hex2bin( 'dcfcdfd970073a4c862f38a98dec4fac' ) ],
			'bytesValidV5' => [ hex2bin( 'a08d16158ad85047bbf8c607c62bd51a' ) ],
			'åäö' => [ 'Räksmörgås' ],
			'æøå' => [ 'Blåbærsyltetøy' ]
		];
	}

}
