<?php
/**
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2014, Harry Burt
 * @license GPL-2.0-or-later
 * @group Database
 */
class TranslateSvgUtilsTest extends TranslateSvgTestCase {
	public function setUp() : void {
		parent::setUp();
		$this->prepareFile( __DIR__ . '/../data/Speech_bubbles.svg' );
	}

	/**
	 * Provides pairings of full string and its component property string (or '' if not applicable)
	 * @return array
	 */
	public function propertyStringProvider() {
		global $wgTranslateSvgTemplateName;
		$tn = $wgTranslateSvgTemplateName;

		return [
			[ "Test", '' ],
			[ "Test$tn", '' ],
			[ 'Test {{' . $tn, '' ],
			[ '{{' . $tn . '}}foo', '' ],
			[ '{{' . $tn . '}}', '{{' . $tn . '}}' ],
			[ 'Test {{' . $tn . '}}', '{{' . $tn . '}}' ],
			[ '{{' . $tn . '|foo=bar}}', '{{' . $tn . '|foo=bar}}' ],
			[ 'foo{{' . $tn . '|foo=bar}}', '{{' . $tn . '|foo=bar}}' ]
		];
	}

	/**
	 * @dataProvider propertyStringProvider
	 * @param string $translation The translation that may contain a property string
	 * @param string $propertyString The property string to expected
	 * @covers TranslateSvgUtils::hasPropertyString
	 */
	public function testHasPropertyString( $translation, $propertyString ) {
		$this->assertEquals( $propertyString !== '', TranslateSvgUtils::hasPropertyString( $translation ) );
	}

	/**
	 * @dataProvider propertyStringProvider
	 * @param string $translation The translation that contains a property string
	 * @param string $propertyString The property string to expected, or '' if no property string is found
	 * @covers TranslateSvgUtils::extractPropertyString
	 */
	public function testExtractPropertyString( $translation, $propertyString ) {
		$this->assertEquals( $propertyString, TranslateSvgUtils::extractPropertyString( $translation ) );
	}

	/**
	 * @dataProvider propertyStringProvider
	 * @param string $translation The translation that contains a property string
	 * @param string $propertyString The property string expected to be stripped out
	 * @covers TranslateSvgUtils::stripPropertyString
	 */
	public function testStripPropertyString( $translation, $propertyString ) {
		// Get the bit *without* the property string
		$expected = str_replace( $propertyString, '', $translation );
		$this->assertEquals( $expected, TranslateSvgUtils::stripPropertyString( $translation ) );
	}

	/**
	 * Provides triples of namespace ids, file names, and whether that page should be recognised as
	 * an SVG filepage
	 * @return array
	 */
	public function titleProvider() {
		return [
			[ NS_FILE, false, true ],
			[ NS_MAIN, false, false ],
			[ NS_FILE, 'Speech_bubbles.jpg', false ]
		];
	}

	/**
	 * @dataProvider titleProvider
	 * @param int $ns The namespace to look up the page in (e.g. NS_FILE)
	 * @param bool|string $name The name of the filepage to test, or false to use self::$name
	 * @param bool $isFilePage The expected output i.e. whether the page is an SVG filepage or not
	 * @covers TranslateSvgUtils::isSVGFilePage
	 */
	public function testIsSVGFilePage( $ns, $name, $isFilePage ) {
		if ( !$name ) {
			$name = self::$name; // Providers are established before self::$name is set
		}
		$title = Title::makeTitle( $ns, $name );
		$this->assertEquals( $isFilePage, TranslateSvgUtils::isSVGFilePage( $title ) );
	}

	/**
	 * Provides triples of parameter name, parameter value, and sanitised output (a pairing of
	 * attribute name and value)
	 * @return array
	 */
	public function paramProvider() {
		return [
			[ 'hackingAttempt', 'inject', [ false, false ] ],
			[ 'bold', 'yes', [ 'font-weight', 'bold' ] ],
			[ 'underline', 'yes', [ 'text-decoration', 'underline' ] ],
			[ 'italic', 'no  ', [ 'font-style', 'normal' ] ], // Trim value
			[ ' sodipodi:role', 'line', [ 'sodipodi:role', 'line' ] ], // Trim parameter
			[ 'xml:type', 'test', [ 'xml:type', 'test' ] ], // Custom parameter pass-through
			[ 'xml:type', '', [ 'xml:type', false ] ] // '' becomes false
		];
	}

	/**
	 * @dataProvider paramProvider
	 * @param string $name The parameter name provided
	 * @param string $value The parameter value provided
	 * @param array $array The expected output i.e. a sanitised pairing of attribute name and value
	 * @covers TranslateSvgUtils::mapToAttribute
	 */
	public function testMapToAttribute( $name, $value, $array ) {
		global $wgTranslateSvgOptionalProperties;
		$wgTranslateSvgOptionalProperties[] = 'xml:type';
		$this->assertArrayEquals( $array, TranslateSvgUtils::mapToAttribute( $name, $value ) );
	}

	/**
	 * Provides triples of attribute name, attribute value, and sanitised output (a pairing of
	 * parameter name and value)
	 * @return array
	 */
	public function attribProvider() {
		return [
			[ 'hackingAttempt', 'inject', [ false, false ] ],
			[ 'font-weight', 'bold', [ 'bold', 'yes' ] ],
			[ 'text-decoration', 'underline', [ 'underline', 'yes' ] ],
			[ 'font-style', 'normal  ', [ 'italic', 'no' ] ], // Trim value
			[ ' sodipodi:role', 'line', [ 'sodipodi:role', 'line' ] ], // Trim parameter
			[ 'fill', '#ccc', [ 'color', '#ccc' ] ], // fill to color
			[ 'xml:type', 'test', [ 'xml:type', 'test' ] ], // Custom parameter pass-through
			[ 'xml:type', '', [ 'xml:type', false ] ], // '' becomes false
			[ 'font-family', 'Sans', [ 'font-family', 'sans-serif' ] ] // Hard coded mapping
		];
	}

	/**
	 * @dataProvider attribProvider
	 * @param string $name The attribute name provided
	 * @param string $value The attribute value provided
	 * @param array $array The expected output i.e. a sanitised pairing of parameter name and value
	 * @covers TranslateSvgUtils::mapFromAttribute
	 */
	public function testMapFromAttribute( $name, $value, $array ) {
		global $wgTranslateSvgOptionalProperties;
		$wgTranslateSvgOptionalProperties[] = 'xml:type';
		$this->assertArrayEquals( $array, TranslateSvgUtils::mapFromAttribute( $name, $value ) );
	}

	/**
	 * Provides pairs of translation strings and their associated arrays
	 * @return array
	 */
	public function translationProvider() {
		global $wgTranslateSvgTemplateName;
		$tn = $wgTranslateSvgTemplateName;
		return [
			[
				'Foo{{' . $tn . '|x=|y=|font-family=other|font-size=|units=other|color=' .
					'|underline=no|italic=no|bold=yes}}',
				[
					'text' => 'Foo',
					'font-weight' => 'bold',
					'text-decoration' => 'normal',
					'font-style' => 'normal',
				]
			],
			[
				'Foo{{' . $tn . '|x=|y=|font-family=other|font-size=12|units=px|color=' .
					'|underline=no|italic=no|bold=yes}}',
				[
					'text' => 'Foo',
					'font-weight' => 'bold',
					'text-decoration' => 'normal',
					'font-style' => 'normal',
					'font-size' => '12px',
				]
			]
		];
	}

	/**
	 * @dataProvider translationProvider
	 * @param string $translation  The translation string (text plus properties) to transform
	 * @param array $array The expected output, i.e. the full translation array
	 * @covers TranslateSvgUtils::translationToArray
	 */
	public function testTranslationToArray( $translation, $array ) {
		$this->assertArrayEquals( $array, TranslateSvgUtils::translationToArray( $translation ) );
	}

	/**
	 * @dataProvider translationProvider
	 * @param string $translation The expected output, i.e. the full translation string (text plus properties)
	 * @param array $array The translation array to transform
	 * @covers TranslateSvgUtils::arrayToTranslation
	 */
	public function testArrayToTranslation( $translation, $array ) {
		$this->assertEquals( $translation, TranslateSvgUtils::arrayToTranslation( $array ) );
	}

	/**
	 * Provides triples of 'operating system' language codes, MediaWiki language codes, and language names
	 * @return array
	 */
	public function langProvider() {
		return [
			[ 'en_GB', 'en-gb', 'British English' ],
			[ 'fr', 'fr', 'français' ],
			[ 'fr_BZ', 'fr-bz', 'français' ], // fr-bz doesn't exist in ISO but fr does
			[ 'zz_BZ', 'zz-bz', 'zz-bz' ], // neither zz-bz nor zz doesn't exist in ISO
			[ 'fallback', 'fallback', 'Deutsch' ] // fallback defined as de
		];
	}

	/**
	 * @dataProvider langProvider
	 * @param string $os The 'operating system' form of the name to transform, e.g. pt_BR
	 * @param string $langCode The expected output, i.e. the 'lang code' form of the name, e.g. pt-br
	 * @covers TranslateSvgUtils::osToLangCode
	 */
	public function testOsToLangCode( $os, $langCode ) {
		$this->assertEquals( $langCode, TranslateSvgUtils::osToLangCode( $os ) );
	}

	/**
	 * @dataProvider langProvider
	 * @param string $os The expected output, i.e. 'operating system' form of the name, e.g. pt_BR
	 * @param string $langCode The 'lang code' form of the name to transform, e.g. pt-br
	 * @covers TranslateSvgUtils::langCodeToOs
	 */
	public function testLangCodeToOs( $os, $langCode ) {
		$this->assertEquals( $os, TranslateSvgUtils::langCodeToOs( $langCode ) );
	}

	/**
	 * @dataProvider langProvider
	 * @param string $os (Unused.)
	 * @param string $langCode The 'lang code' form of the name, e.g. fr
	 * @param string $langName The expected language name e.g. français
	 * @covers TranslateSvgUtils::fetchLanguageName
	 */
	public function testFetchLanguageName( $os /* unused */, $langCode, $langName ) {
		$this->assertEquals( $langName, TranslateSvgUtils::fetchLanguageName( $langCode, 'de' ) );
	}
}
