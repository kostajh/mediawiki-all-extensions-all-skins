<?php

namespace MediaWiki\Extension\EventStreamConfig;

use InvalidArgumentException;
use MediaWikiUnitTestCase;

/**
 * @covers \MediaWiki\Extension\EventStreamConfig\StreamConfig
 * @group EventStreamConfig
 */
class StreamConfigTest extends MediaWikiUnitTestCase {

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::stream()
	 */
	public function testStream() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( 'nonya', $streamConfig->stream() );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::toArray()
	 */
	public function testToArray() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];

		$expected = [
			'sample_rate' => 0.5,
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( $expected, $streamConfig->toArray() );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::toArray()
	 */
	public function testToArrayAllSettingsWithDefaults() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
			'topics' => [ 'nonya' ],
		];

		$defaultSettings = [
			'is_active' => true
		];

		$expected = $settings + $defaultSettings;

		$streamConfig = new StreamConfig( $settings, $defaultSettings );
		$this->assertEquals( $expected, $streamConfig->toArray( true ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::toArray()
	 */
	public function testToArrayWithTopicPrefixesAllSettings() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
			'topic_prefixes' => [ 'eqiad.', 'codfw.' ],
		];

		$expected = $settings;
		$expected['topics'] = [ 'eqiad.nonya', 'codfw.nonya' ];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( $expected, $streamConfig->toArray( true ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matches()
	 */
	public function testMatchesString() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertTrue( $streamConfig->matches( 'nonya' ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matches()
	 */
	public function testMatchesRegex() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'sample_rate' => 0.8,
			'destination_event_service' => 'eventgate-main',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertTrue( (bool)$streamConfig->matches( 'mediawiki.job.workworkwork' ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matches()
	 */
	public function testGivenRegexStreamDoesNotMatch() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'sample_rate' => 0.8,
			'destination_event_service' => 'eventgate-main',
		];

		$streamConfig = new StreamConfig( $settings );
		// Since the stream setting should be recognized as a regex, string equivalence
		// should not be used to match the incoming target stream name, and
		// preg_match( $regex, $regex ) will be false.
		$this->assertFalse( (bool)$streamConfig->matches( '/^mediawiki\.job\..+/' ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::__construct()
	 */
	public function testMissingStreamName() {
		$settings = [
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];
		$this->expectException( InvalidArgumentException::class );
		new StreamConfig( $settings );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::__construct()
	 */
	public function testWrongStreamNameType() {
		$settings = [
			'stream' => 10.0,
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];
		$this->expectException( InvalidArgumentException::class );
		new StreamConfig( $settings );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::__construct()
	 */
	public function testInvalidStreamNameRegex() {
		$settings = [
			'stream' => '/nonya/BADREGEX',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];
		$this->expectException( InvalidArgumentException::class );
		new StreamConfig( $settings );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matchesSettings()
	 */
	public function testMatchesSettings() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];

		$constraints = [
			'destination_event_service' => 'eventgate-analytics',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertTrue( $streamConfig->matchesSettings( $constraints ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matchesSettings()
	 */
	public function testMatchesSettingsBoolean() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
			'canary_events_enabled' => true,
		];

		$constraints = [
			'canary_events_enabled' => true,
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertTrue( $streamConfig->matchesSettings( $constraints ) );
	}

	/**
	 * PHP's array_intersect_assoc casts true to "1" before comparing.
	 * So, if we want to compare with a string constraint value to a boolean setting,
	 * we must pre-cast the constraint value to "1".
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matchesSettings()
	 */
	public function testMatchesSettingsBooleanStringTrue() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
			'canary_events_enabled' => true,
		];

		$constraints = [
			'canary_events_enabled' => "1",
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertTrue( $streamConfig->matchesSettings( $constraints ) );
	}

	/**
	 * PHP's array_intersect_assoc casts false to "" before comparing.
	 * So, if we want to compare with a string constraint value to a boolean setting,
	 * we must pre-cast the constraint value to "".
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matchesSettings()
	 */
	public function testMatchesSettingsBooleanStringFalse() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
			'canary_events_enabled' => false,
		];

		$constraints = [
			'canary_events_enabled' => "",
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertTrue( $streamConfig->matchesSettings( $constraints ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matchesSettings()
	 */
	public function testNotMatchesSettings() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];

		$constraints = [
			'destination_event_service' => 'eventgate-main',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertFalse( $streamConfig->matchesSettings( $constraints ) );
	}

	/**
	 * @covers MediaWiki\Extension\EventStreamConfig\StreamConfig::matchesSettings()
	 */
	public function testMatchesSettingsStreamRegex() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'destination_event_service' => 'eventgate-main',
		];

		$constraints = [
			'stream' => 'mediawiki.job.workworkwork',
			'destination_event_service' => 'eventgate-main',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertTrue( $streamConfig->matchesSettings( $constraints ) );
	}

	public function testTopicsWithExplicitTopicsSetting() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
			'topics' => [ 'eqiad.nonya', 'codfw.nonya' ],
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( $streamConfig->topics(), $settings['topics'] );
	}

	public function testTopicsWithoutTopicPrefixes() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( $streamConfig->topics(), [ 'nonya' ] );
	}

	public function testTopicsWithTopicPrefixes() {
		$settings = [
			'stream' => 'nonya',
			'schema_title' => 'mediawiki/nonya',
			'sample_rate' => 0.5,
			'destination_event_service' => 'eventgate-analytics',
			'topic_prefixes' => [ 'eqiad.', 'codfw.' ],
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( $streamConfig->topics(), [ 'eqiad.nonya', 'codfw.nonya' ] );
	}

	public function testTopicsStreamRegexSettingWithoutTopicPrefixes() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'destination_event_service' => 'eventgate-main',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( $streamConfig->topics(), [ $settings['stream'] ] );
	}

	public function testTopicsStreamRegexSettingWithTopicPrefixes() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'destination_event_service' => 'eventgate-main',
			'topic_prefixes' => [ 'eqiad.', 'codfw.' ],
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals( $streamConfig->topics(), [ '/^(eqiad\.|codfw\.)mediawiki\.job\..+/' ] );
	}

	public function testTopicsTargetStreamNameWithoutTopicPrefixes() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'destination_event_service' => 'eventgate-main',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals(
			$streamConfig->topics( 'mediawiki.job.workworkwork' ),
			[ 'mediawiki.job.workworkwork' ]
		);
	}

	public function testTopicsTargetStreamNameWithTopicPrefixes() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'destination_event_service' => 'eventgate-main',
			'topic_prefixes' => [ 'eqiad.', 'codfw.' ],
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals(
			$streamConfig->topics( 'mediawiki.job.workworkwork' ),
			[ 'eqiad.mediawiki.job.workworkwork', 'codfw.mediawiki.job.workworkwork' ]
		);
	}

	public function testTopicsTargetStreamRegexWithoutTopicPrefixes() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'destination_event_service' => 'eventgate-main',
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals(
			$streamConfig->topics( '/^mediawiki\.job\..+/' ),
			[ '/^mediawiki\.job\..+/' ]
		);
	}

	public function testTopicsTargetStreamRegexWithTopicPrefixes() {
		$settings = [
			'stream' => '/^mediawiki\.job\..+/',
			'schema_title' => 'mediawiki/job',
			'destination_event_service' => 'eventgate-main',
			'topic_prefixes' => [ 'eqiad.', 'codfw.' ],
		];

		$streamConfig = new StreamConfig( $settings );
		$this->assertEquals(
			$streamConfig->topics( '/^mediawiki\.job\..+/' ),
			[ '/^(eqiad\.|codfw\.)mediawiki\.job\..+/' ]
		);
	}

}
