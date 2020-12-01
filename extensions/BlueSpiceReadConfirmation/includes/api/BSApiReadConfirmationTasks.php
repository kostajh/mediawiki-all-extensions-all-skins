<?php

use BlueSpice\Api\Response\Standard;
use BlueSpice\ReadConfirmation\IMechanism;
use BlueSpice\ReadConfirmation\MechanismFactory;

class BSApiReadConfirmationTasks extends BSApiTasksBase {

	/**
	 * @var string
	 */
	protected $sTaskLogType = 'bs-readconfirmation';

	/**
	 * @var array
	 */
	protected $aTasks = [ 'confirm', 'check', 'remind' ];

	/**
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'confirm' => [ 'read' ],
			'check' => [ 'read' ],
			'remind' => [ 'readconfirmationremind' ]
		];
	}

	/**
	 * @param stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	protected function task_confirm( $taskData, $params ) {
		$result = $this->makeStandardReturn();

		if ( empty( $taskData->pageId ) ) {
			$result->message = $this->msg( 'bs-readconfirmation-api-error-no-page' )->plain();
			return $result;
		}
		$title = Title::newFromId( $taskData->pageId );
		if ( !$title ) {
			$result->message = $this->msg( 'bs-readconfirmation-api-error-no-page' )->plain();
			return $result;
		}
		$revision = $this->getServices()->getRevisionStore()->getRevisionByTitle( $title );
		$revId = $revision ? $revision->getId() : 0;

		if ( isset( $taskData->isStableRevision ) && $taskData->isStableRevision === true ) {
			if ( isset( $taskData->stableRevId ) ) {
				$revId = $taskData->stableRevId;
			}
		}

		$mechanismInstance = $this->getMechanismInstance();

		if ( $mechanismInstance->canConfirm( $title, $this->getUser(), $revId ) ) {
			$mechanismInstance->confirm( $title, $this->getUser(), $revId );
			$this->logTaskAction( 'confirm', [ 'revid' => $revId ], [ 'target' => $title ] );
			$result->success = true;
		} else {
			$result->message = $this->msg( 'bs-readconfirmation-api-error-cant-confirm' )->plain();
		}

		return $result;
	}

	/**
	 * @param stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	protected function task_check( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$mechanismInstance = $this->getMechanismInstance();

		if ( empty( $taskData->pageId ) ) {
			$result->message = $this->msg( 'bs-readconfirmation-api-error-no-page' )->plain();
			return $result;
		}
		$title = Title::newFromId( $taskData->pageId );
		if ( !$title ) {
			$result->message = $this->msg( 'bs-readconfirmation-api-error-no-page' )->plain();
			return $result;
		}
		$revision = $this->getServices()->getRevisionStore()->getRevisionByTitle( $title );
		$revId = $revision ? $revision->getId() : 0;

		if ( isset( $taskData->isStableRevision ) && $taskData->isStableRevision === true ) {
			if ( isset( $taskData->stableRevId ) ) {
				$revId = $taskData->stableRevId;
			}
		}

		$result->success = true;
		$result->payload = [
			'pageId' => $taskData->pageId,
			'userId' => $this->getUser()->getId(),
			'userHasConfirmed' => true
		];

		if ( $mechanismInstance->canConfirm( $title, $this->getUser(), $revId ) ) {
			$result->payload[ 'userHasConfirmed' ] = false;
		}

		return $result;
	}

	/**
	 * @param stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	protected function task_remind( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$title = Title::newFromID( $taskData->pageId );
		$mechanismInstance = $this->getMechanismInstance();

		$notifiedUsers = $mechanismInstance->notify( $title, $this->getUser() );

		if ( $notifiedUsers == false ) {
			return $result;
		}

		$userDisplayNames = [];
		foreach ( $notifiedUsers as $notifiedUser ) {
			$userDisplayNames[] = $this->getServices()->getService( 'BSUtilityFactory' )
				->getUserHelper( $notifiedUser )->getDisplayName();
		}

		$this->logTaskAction(
			'remind',
			[ '4::users' => implode( ', ',  $userDisplayNames ) ],
			[ 'target' => $title ]
		);

		$result->success = true;
		return $result;
	}

	/**
	 *
	 * @return bool
	 */
	public function isWriteMode() {
		return true;
	}

	/**
	 * Creates a log entry for Special:Log, based on $this->sTaskLogType or
	 * $aOptions['type']. See https://www.mediawiki.org/wiki/Manual:Logging_to_Special:Log
	 * @param string $sAction
	 * @param array $aParams for the log entry
	 * @param array $aOptions <br/>
	 * 'performer' of type User<br/>
	 * 'target' of type Title<br/>
	 * 'timestamp' of type string<br/>
	 * 'relations of type array<br/>
	 * 'deleted' of type int<br/>
	 * 'type' of type string; to allow overriding of class default
	 * @param bool $bDoPublish
	 * @return int Id of the newly created log entry or -1 on error
	 */
	protected function logTaskAction( $sAction, $aParams, $aOptions = [], $bDoPublish = false ) {
		$aOptions += [
			'performer' => null,
			'target' => null,
			'timestamp' => null,
			'relations' => null,
			'comment' => null,
			'deleted' => null,
			'publish' => null,
			// To allow overriding of class default
			'type' => null
		];

		$oTarget = $aOptions['target'];
		if ( $oTarget === null ) {
			$oTarget = $this->makeDefaultLogTarget();
		}

		$oPerformer = $aOptions['performer'];
		if ( $oPerformer === null ) {
			$oPerformer = $this->getUser();
		}

		$sType = $this->sTaskLogType;
		if ( $aOptions['type'] !== null ) {
			$sType = $aOptions['type'];
		}

		// Not set on class, not set as call option
		if ( $sType === null ) {
			return -1;
		}

		$oLogger = new ManualLogEntry( $sType, $sAction );
		$oLogger->setPerformer( $oPerformer );
		$oLogger->setTarget( $oTarget );
		$oLogger->setParameters( $aParams );

		if ( $aOptions['timestamp'] !== null ) {
			$oLogger->setTimestamp( $aOptions['timestamp'] );
		}

		if ( $aOptions['relations'] !== null ) {
			$oLogger->setRelations( $aOptions['relations'] );
		}

		if ( $aOptions['comment'] !== null ) {
			$oLogger->setComment( $aOptions['comment'] );
		}

		if ( $aOptions['deleted'] !== null ) {
			$oLogger->setDeleted( $aOptions['deleted'] );
		}

		$iLogEntryId = $oLogger->insert();

		if ( $bDoPublish ) {
			$oLogger->publish( $iLogEntryId );
		}

		return $iLogEntryId;
	}

	/**
	 * @return IMechanism
	 */
	private function getMechanismInstance() {
		/** @var MechanismFactory $factory */
		$factory = $this->getServices()->getService(
			'BSReadConfirmationMechanismFactory'
		);

		return $factory->getMechanismInstance();
	}
}
