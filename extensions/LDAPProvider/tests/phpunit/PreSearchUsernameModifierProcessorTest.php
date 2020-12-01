<?php

namespace MediaWiki\Extension\LDAPProvider\Tests;

use MediaWiki\Extension\LDAPProvider\PreSearchUsernameModifierProcessor;
use PHPUnit\Framework\TestCase;

class PreSearchUsernameModifierProcessorTest extends TestCase {

	/**
	 * @covers PreSearchUsernameModifierProcessor::process
	 * @dataProvider provideTestProcessData
	 * @param string[] $enabledModifier
	 * @param string $originalUsername
	 * @param string $exprectedModifiedUsername
	 * @return void
	 */
	public function testProcess( $enabledModifier, $originalUsername, $exprectedModifiedUsername ) {
		$modifierRegistry = [
			"removespaces" => "\\MediaWiki\\Extension\\LDAPProvider"
				. "\\PreSearchUsernameModifier\\RemoveSpaces::newInstance",
			"spacestounderscores" => "\\MediaWiki\\Extension\\LDAPProvider"
				. "\\PreSearchUsernameModifier\\SpacesToUnderscores::newInstance",
			"lowercase" => "\\MediaWiki\\Extension\\LDAPProvider"
				. "\\PreSearchUsernameModifier\\ToLower::newInstance"
		];

		$chain = new PreSearchUsernameModifierProcessor( $modifierRegistry );
		$modifiedUsername = $chain->process( $originalUsername, $enabledModifier );

		$this->assertEquals( $exprectedModifiedUsername, $modifiedUsername );
	}

	/**
	 *
	 * @return array
	 */
	public function provideTestProcessData() {
		return [
			[
				[ 'removespaces' ],
				'SoMe niceUsername',
				'SoMeniceUsername'
			],
			[
				[ 'removespaces', 'lowercase' ],
				'SoMe niceUsername',
				'someniceusername'
			],
			[
				[ 'spacestounderscores' ],
				'SoMe niceUsername',
				'SoMe_niceUsername'
			],
			[
				[ 'spacestounderscores', 'lowercase' ],
				'SoMe niceUsername',
				'some_niceusername'
			]
		];
	}
}
