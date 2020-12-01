<?php
namespace BlueSpice\Readers\Api\Task;

use BlueSpice\Api\Response\Standard;
use BlueSpice\Readers\Job\InsertTrace as Job;
use JobQueueGroup;
use MediaWiki\MediaWikiServices;

class PageReaders extends \BSApiTasksBase {

	/**
	 * @var array
	 */
	protected $aTasks = [
		'insertTrace'
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'insertTrace' => [ 'read' ]
		];
	}

	/**
	 *
	 * @param \stdClass $taskData
	 * @param array $params
	 * @return Standard
	 *
	 */
	// phpcs:ignore
	protected function task_insertTrace( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();
		if ( $this->skipRequest() ) {
			$result->success = false;
			return $result;
		}

		$job = new Job( $this->getContext()->getTitle(), [
			Job::PARAM_USER_ID => $this->getContext()->getUser()->getId(),
			Job::PARAM_TIMESTAMP => wfTimestampNow(),
			Job::PARAM_REV_ID => $taskData->revId ? $taskData->revId : $this->getTitle()->getLatestRevID()
		] );

		JobQueueGroup::singleton()->push( $job );

		$result->success = true;
		return $result;
	}

	/**
	 * @return bool
	 */
	protected function skipRequest() {
		if ( $this->getServices()->getReadOnlyMode()->isReadOnly() ) {
			return true;
		}
		if ( !$this->getTitle() || !$this->getTitle()->exists() ) {
			return true;
		}

		$canRead = MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan(
				'read',
				$this->getUser(),
				$this->getTitle()
			);

		if ( !$canRead ) {
			return true;
		}

		if ( $this->getUser()->isAnon() ) {
			return true;
		}
		// Not sure if this is needed additionaly to isAnon...
		if ( \User::isIP( $this->getUser()->getName() ) ) {
			return true;
		}
		$excludeNS = $this->getConfig()->get( 'ReadersNamespaceBlacklist' );
		if ( in_array( $this->getTitle()->getNamespace(), $excludeNS ) ) {
			return true;
		}
		return false;
	}

}
