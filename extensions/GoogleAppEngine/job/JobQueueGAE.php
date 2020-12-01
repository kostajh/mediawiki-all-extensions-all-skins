<?php

use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;

class JobQueueGAE extends JobQueue {

	protected function supportedOrders() {
		return array( 'undefined', 'random' );
	}

	protected function optimalOrder() {
		return 'undefined';
	}

	protected function supportsDelayedJobs() {
		// @todo GAE does support these
		return false;
	}

	protected function doIsEmpty() {
		return true;
	}

	protected function doGetSize() {
		return 0;
	}

	protected function doGetAcquiredCount() {
		return 0;
	}

	protected function doBatchPush( array $jobs, $flags ) {
		$tasks = array();
		foreach( $jobs as $job ) {
			$tasks[] = $this->convertJobToTask( $job );
		}

		$queue = new PushQueue();
		$queue->addTasks($tasks);
		return true;
	}

	protected function doPop() {
		return false;
	}

	protected function doAck( Job $job ) {
	}

	public function getAllQueuedJobs() {
		return new ArrayIterator( array() );
	}

	/**
	 * Converts a MediaWiki job into a GAE task
	 * @param Job $job
	 * @return PushTask
	 */
	private function convertJobToTask( Job $job ) {
		return new PushTask(
			wfScript( 'api' ),
			array(
				'action' => 'gaejobqueue',
				'format' => 'json',
				'type' => $job->getType(),
				'namespace' => $job->getTitle()->getNamespace(),
				'titletext' => $job->getTitle()->getText(),
				'params' => serialize( $job->getParams() ),
			)
		);
	}
}