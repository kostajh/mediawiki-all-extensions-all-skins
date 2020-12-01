<?php

if ( file_exists( __DIR__ . '/../../vendor/autoload.php' ) ) {
	include_once __DIR__ . '/../../vendor/autoload.php';
}

/**
 * @group FundraisingEmailUnsubscribe
 */
class SubscribeTest extends MediaWikiTestCase {
	/**
	 * @var FundraiserSubscribe
	 */
	protected $processor;

	public function setUp() : void {
		parent::setUp();
		$this->setMwGlobals( [
			'wgFundraisingEmailUnsubscribeQueueClass' => \PHPQueue\Backend\PDO::class,
			'wgFundraisingEmailUnsubscribeQueueParameters' => [
				'opt-in' => [
					'connection_string' => 'sqlite::memory:',
					'queue' => 'opt_in', // this backend needs a valid table name
				],
			],
		] );
		$this->processor = new FundraiserSubscribe();
	}

	public function testSendMessage() {
		$id = mt_rand();
		$this->processor->update(
			$id, 'optin', [
				'email' => 'donor@example.com',
				'variant' => '',
			]
		);
		$queue = FundraiserEmailQueue::get( 'opt-in' );
		$message = $queue->pop();
		$this->assertNotNull( $message );
		$this->assertEquals( [
			'email' => 'donor@example.com',
			'process' => 'optin',
			'variant' => ''
		], $message );
	}

	public function testSendMessageVariant() {
		$id = mt_rand();
		$this->processor->update(
			$id, 'optin', [
				'email' => 'donor@example.com',
				'variant' => 'bloop_de_woop',
			]
		);
		$queue = FundraiserEmailQueue::get( 'opt-in' );
		$message = $queue->pop();
		$this->assertNotNull( $message );
		$this->assertEquals( [
			'email' => 'donor@example.com',
			'process' => 'optin',
			'variant' => 'bloop_de_woop'
		], $message );
	}
}
