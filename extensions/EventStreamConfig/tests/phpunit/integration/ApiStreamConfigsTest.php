<?php

namespace MediaWiki\Extension\EventStreamConfig;

use ApiMain;
use ApiResult;
use ApiTestCase;
use FauxRequest;
use RequestContext;

/**
 * @covers \MediaWiki\Extension\EventStreamConfig\ApiStreamConfigs
 * @group EventStreamConfig
 * @group medium
 */
class ApiStreamConfigsTest extends ApiTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->setMwGlobals( 'wgEventStreams', [
			[
				StreamConfig::STREAM_SETTING => 'test',
				StreamConfig::TOPICS_SETTING => [ 'topic' ],
				StreamConfig::TOPIC_PREFIXES_SETTING => [ 'prefix.' ],
				'other-setting' => [],
				'true-setting' => true,
				'false-setting' => false,
			],
		] );
	}

	public function testResultArrayFormat(): void {
		$result = $this->doRequest( [ 'action' => 'streamconfigs', 'all_settings' => true ] );
		$this->assertSame( 'assoc', $result['streams'][ApiResult::META_TYPE] );

		$testConfig = $result['streams']['test'];
		$this->assertSame( 'assoc', $testConfig[ApiResult::META_TYPE] );
		$this->assertArrayNotHasKey( ApiResult::META_TYPE, $testConfig[StreamConfig::TOPICS_SETTING] );
		$this->assertArrayNotHasKey( ApiResult::META_TYPE, $testConfig[StreamConfig::TOPIC_PREFIXES_SETTING] );
		$this->assertSame( 'assoc', $testConfig['other-setting'][ApiResult::META_TYPE] );

		// Make sure true and false are serialized correctly in output using formatversion=2
		$this->assertSame( true, $result['streams']['test']['true-setting'] );
		$this->assertSame( false, $result['streams']['test']['false-setting'] );
	}

	/**
	 * Custom API request function needed because ApiTestCase::doApiRequest unconditionally
	 * strips result metadata, which we need to test.
	 * @param array $params
	 * @return mixed
	 */
	private function doRequest( array $params ) {
		global $wgRequest;
		$wgRequest = new FauxRequest( $params, true );
		RequestContext::getMain()->setRequest( $wgRequest );
		$context = $this->apiContext->newTestContext( $wgRequest );
		$module = new ApiMain( $context );
		$module->execute();
		return $module->getResult()->getResultData();
	}

}
