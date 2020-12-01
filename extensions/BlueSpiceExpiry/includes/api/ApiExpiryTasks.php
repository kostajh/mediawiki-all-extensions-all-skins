<?php

use BlueSpice\Api\Response\Standard;

class ApiExpiryTasks extends BSApiTasksBase {
	/**
	 *
	 * @var string[]
	 */
	protected $aTasks = [ 'saveExpiry', 'deleteExpiry', 'getDetailsForExpiry' ];

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_getDetailsForExpiry( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		$iArticleId = isset( $oTaskData->articleId )
			? (int)$oTaskData->articleId
			: 0;

		$oTitle = Title::newFromID( $iArticleId );

		$aExpiry = \BlueSpice\Expiry\Extension::getExpiryForPage( $iArticleId );

		if ( !$aExpiry ) {
			$oResult->message = $oResult->errors['expiryId']
				= wfMessage( 'bs-expiry-unknown-page-msg' )->plain();
			return $oResult;
		}

		$aReturnData = [];
		$aReturnData['date'] = $aExpiry['exp_date'];
		$aReturnData['id'] = $aExpiry['exp_id'];
		$aReturnData['comment'] = $aExpiry['exp_comment'];

		$oResult->success = true;
		$oResult->payload = $aReturnData;
		return $oResult;
	}

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_saveExpiry( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$oUser = $this->getUser();
		$bIsUpdate = false;
		if ( $oUser->isAnon() ) {
			$oResult->message = $oResult->errors['permissionError']
				= wfMessage( 'bs-permissionerror' )->plain();
			return $oResult;
		}

		$setReminder = isset( $oTaskData->setReminder ) && $oTaskData->setReminder == true
			? true
			: false;
		$sComment = strip_tags( empty( $oTaskData->comment ) ? '' : (string)$oTaskData->comment );

		$iDate = isset( $oTaskData->date )
			? $oTaskData->date
			: 0;
		$dbr = wfGetDB( DB_REPLICA );
		// this is normally the case when clicking the expiry on a normal page
		// (not the overview specialpage) or the edit button on the specialpage
		// and data needs to be prefilled
		if ( empty( $oTaskData->articleId ) && !empty( $oTaskData->id ) ) {
			$res = $dbr->select( 'bs_expiry', 'exp_page_id', [ 'exp_id' => (int)$oTaskData->id ] );
			if ( !$res ) {
				$oResult->message = $oResult->errors['noactions'] =
					wfMessage( 'bs-expiry-unknown-page-msg' )->text();
			}
			$row = $res->fetchRow();
			if ( empty( $row['exp_page_id'] ) ) {
				$oResult->message = $oResult->errors['noactions'] =
					wfMessage( 'bs-expiry-unknown-page-msg' )->text();
				return $oResult;
			}
			$oTaskData->articleId = (int)$row['exp_page_id'];
		}
		if ( empty( $oTaskData->articleId ) && !empty( $oTaskData->pageName ) ) {
			$oTitle = Title::newFromText( $oTaskData->pageName );
		} else {
			$oTitle = Title::newFromID( $oTaskData->articleId );
		}

		if ( !$oTitle instanceof Title || !$oTitle->exists() ) {
			$oResult->message = $oResult->errors['unknown-page'] =
				wfMessage( 'bs-expiry-unknown-page-msg' )->text();
			return $oResult;
		}
		// TODO: is valid date?
		$sFormattedFieldValue = date( "Y-m-d", $iDate );

		$res = $dbr->select(
			'bs_expiry',
			'*',
			[
				'exp_page_id' => (int)$oTitle->getArticleID()
			],
			__METHOD__
		);
		$iExpiryId = 0;
		if ( $res && $res->numRows() ) {
			$row = $res->fetchRow();
			$iExpiryId = (int)$row['exp_id'];
			$bIsUpdate = true;
		}

		$aData = [
			'exp_page_id' => (int)$oTitle->getArticleID(),
			'exp_date' => $sFormattedFieldValue,
			'exp_comment' => $sComment
		];

		$dbw = wfGetDB( DB_MASTER );
		if ( !$iExpiryId ) {
			$res = $dbw->insert( 'bs_expiry', $aData, __METHOD__ );
			if ( !$res ) {
				$oResult->message = $oResult->errors['createerror'] =
					wfMessage( 'bs-expiry-create-error' )->text();
				return $oResult;
			}

			$iExpiryId = $dbw->insertId();

			try {
				$this->getServices()->getHookContainer()->run( 'BsExpiryOnSave', [
					$oTaskData,
					$iExpiryId,
					// deprecated
					$oTitle->getArticleID(),
					// deprecated
					$oUser->getId(),
					$oUser,
					$oTitle,
				] );
			} catch ( Exception $e ) {
				$oResult->message = $oResult->errors['createerror'] =
					$e->getMessage();
				return $oResult;
			}
		} else {
			$res = $dbw->update( 'bs_expiry', $aData, [ 'exp_id' => $iExpiryId ] );
			if ( !$res ) {
				$oResult->message = $oResult->errors['updateerror'] =
					wfMessage( 'bs-expiry-update-error' )->text();
				return $oResult;
			}

			try {
				$this->getServices()->getHookContainer()->run( 'BsExpiryOnUpdate', [
					$oTaskData,
					$iExpiryId,
					$oUser,
					$oTitle,
				] );
			} catch ( Exception $e ) {
				$oResult->message = $oResult->errors['createerror'] =
					$e->getMessage();
				return $oResult;
			}
		}

		$oResult->success = true;
		$this->setReminders( $aData, $setReminder );

		$oTitle->invalidateCache();

		if ( $bIsUpdate ) {
			$oResult->message = wfMessage( "bs-expiry-update-success" )->plain();
		} else {
			$oResult->message = wfMessage( "bs-expiry-save-success" )->plain();
		}

		return $oResult;
	}

	/**
	 *
	 * @param array $expiryData
	 * @param bool|false $addNewReminders
	 * @return bool
	 */
	private function setReminders( $expiryData, $addNewReminders = false ) {
		if ( !$this->getServices()->hasService( 'BSReminderFactory' ) ) {
			return false;
		}
		$types = $this->getServices()->getService( 'BSReminderFactory' )->getRegisteredTypes();
		if ( !in_array( 'expiry', $types ) ) {
			return false;
		}
		$res = $this->getDB( DB_REPLICA )->select(
			'bs_reminder',
			'*',
			[ 'rem_page_id' => $expiryData['exp_page_id'],
			'rem_type' => 'expiry'
		] );
		$userReminderUpdated = false;
		foreach ( $res as $row ) {
			if ( $row->rem_user_id === $this->getUser()->getId() ) {
				$userReminderUpdated = true;
			}
			$row->rem_date = $expiryData['exp_date'];
			$row->rem_comment = $expiryData['exp_comment'];
			if ( empty( $row->rem_comment ) ) {
				$user = User::newFromId( $row->rem_user_id );
				if ( !$user ) {
					continue;
				}
				$date = DateTime::createFromFormat( "Y-m-d", $expiryData['exp_date'] );
				$timestamp = $this->getContext()->getLanguage()->userTimeAndDate(
					$date->format( 'YmdHis' ),
					$user
				);
				$msg = $this->msg( 'bs-expiry-flyout-body-hint-expires' )
					->params( [ $timestamp ] )
					->inLanguage( $user->getOption( 'language' ) );
				$row->rem_comment = $msg->parse();
			}
			$row->rem_comment = addslashes( strip_tags( $row->rem_comment ) );
			$this->getDB( DB_MASTER )->update(
				'bs_reminder',
				(array)$row,
				[ 'rem_id' => $row->rem_id ]
			);
		}
		if ( $userReminderUpdated || !$addNewReminders ) {
			return true;
		}
		if ( empty( $expiryData['exp_comment'] ) ) {
			$date = DateTime::createFromFormat( "Y-m-d", $expiryData['exp_date'] );
			$timestamp = $this->getContext()->getLanguage()->userTimeAndDate(
				$date->format( 'YmdHis' ),
				$this->getUser()
			);
			$msg = $this->msg( 'bs-expiry-flyout-body-hint-expires' )
				->params( [ $timestamp ] );
			$expiryData['exp_comment'] = $msg->parse();
		}
		return $this->getDB( DB_MASTER )->insert(
			'bs_reminder',
			[
				'rem_date' => $expiryData['exp_date'],
				'rem_page_id' => $expiryData['exp_page_id'],
				'rem_user_id' => $this->getUser()->getId(),
				'rem_comment' => addslashes( strip_tags( $expiryData['exp_comment'] ) ),
				'rem_type' => 'expiry',
				'rem_is_repeating' => false,
			]
		);
	}

	/**
	 *
	 * @param array $expiryData
	 * @return bool
	 */
	private function deleteReminders( $expiryData ) {
		if ( !$this->getServices()->hasService( 'BSReminderFactory' ) ) {
			return false;
		}
		$types = $this->getServices()->getService( 'BSReminderFactory' )->getRegisteredTypes();
		if ( !in_array( 'expiry', $types ) ) {
			return false;
		}
		return $this->getDB( DB_REPLICA )->delete(
			'bs_reminder',
			[ 'rem_page_id' => $expiryData['exp_page_id'],
				'rem_type' => 'expiry'
			]
		);
	}

	/**
	 *
	 * @param \stdClass $oTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_deleteExpiry( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		// Check if there is a expiryId
		$iExpiryId = (int)$oTaskData->expiryId;
		$iArticleId = (int)$oTaskData->articleId;
		if ( !$iExpiryId ) {
			$oResult->message = $oResult->errors['expiryId'] =
				wfMessage( 'bs-expiry-unexpire-unsuccess' )->text();
			return $oResult;
		}

		// All ok, do the actual deletion
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'bs_expiry',
			[
				'exp_id' => $iExpiryId
			],
			__METHOD__
		);

		$this->deleteReminders( [ 'exp_page_id' => $iArticleId ] );
		$oTitle = Title::newFromID( $iArticleId );
		if ( !empty( $iArticleId ) && $oTitle ) {
			$oTitle->invalidateCache();
		}

		$oResult->success = true;
		$oResult->message = wfMessage( 'bs-expiry-unexpire-success' )->plain();
		return $oResult;
	}

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'getDetailsForExpiry' => [ 'read' ],
			'saveExpiry' => [ 'expirearticle' ],
			'deleteExpiry' => [ 'expiry-delete' ]
		];
	}
}
