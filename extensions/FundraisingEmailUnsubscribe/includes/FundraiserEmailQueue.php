<?php

/**
 * Helper to fetch and stash a PHPQueue backend
 * Basically just holds a static reference so tests can look at the same
 * backend instance as the class being tested
 */
class FundraiserEmailQueue {
	/** @var PHPQueue\Interfaces\FifoQueueStore[] */
	static $instances = [];

	/**
	 * @param string $queueName name of the queue to fetch
	 * @return PHPQueue\Interfaces\FifoQueueStore
	 */
	public static function get( $queueName = 'unsubscribe' ) {
		global $wgFundraisingEmailUnsubscribeQueueClass,
			   $wgFundraisingEmailUnsubscribeQueueParameters;

		if ( empty( self::$instances[$queueName] ) ) {
			if ( empty( $wgFundraisingEmailUnsubscribeQueueParameters[$queueName]['queue'] ) ) {
				$wgFundraisingEmailUnsubscribeQueueParameters[$queueName]['queue'] = $queueName;
			}
			self::$instances[$queueName] = new $wgFundraisingEmailUnsubscribeQueueClass(
				$wgFundraisingEmailUnsubscribeQueueParameters[$queueName]
			);
		}

		return self::$instances[$queueName];
	}
}
