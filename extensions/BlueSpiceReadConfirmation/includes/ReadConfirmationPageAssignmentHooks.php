<?php

use BlueSpice\PageAssignments\Data\Page\PrimaryDataProvider;
use BlueSpice\PageAssignments\Data\Page\Record;
use BlueSpice\ReadConfirmation\IMechanism;
use MediaWiki\MediaWikiServices;

class ReadConfirmationPageAssignmentHooks {
	/**
	 *
	 * @param PrimaryDataProvider $dataProvider
	 * @param Record $dataSet
	 * @param Title $title
	 * @return bool
	 */
	public static function onBSPageStoreDataProviderBeforeAppendRow( $dataProvider,
		$dataSet, $title ) {
		if ( !$dataProvider instanceof PrimaryDataProvider ) {
			return true;
		}
		$dataSet->set( 'all_assignees_have_read', false );
		if ( !self::getReadConfirmationMechanismInstance()->mustRead( $title ) ) {
			return true;
		}
		if ( empty( $dataSet->get( Record::ASSIGNMENTS, [] ) ) ) {
			return true;
		}
		$factory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		if ( !$factory ) {
			return true;
		}
		$target = $factory->newFromTargetTitle( $title );
		if ( !$target ) {
			return true;
		}
		if ( empty( $target->getAssignedUserIDs() ) ) {
			return true;
		}

		$aPageReads = self::getReadConfirmationMechanismInstance()->getCurrentReadConfirmations(
			$target->getAssignedUserIDs(),
			[ (int)$dataSet->get( Record::ID ) ]
		);
		$aUserIdsThatHaveRead = array_keys( $aPageReads[(int)$dataSet->get( Record::ID )] );
		$read = empty( array_diff(
			$target->getAssignedUserIDs(),
			$aUserIdsThatHaveRead
		) );
		$dataSet->set( 'all_assignees_have_read', $read );
		return true;
	}

	/**
	 *
	 * @param \BSApiExtJSStoreBase $oApiModule
	 * @param array &$aData
	 * @return bool
	 */
	public static function onBSApiExtJSStoreBaseBeforePostProcessData( $oApiModule, &$aData ) {
		if ( $oApiModule instanceof BSApiMyPageAssignmentStore ) {
			self::extendBSApiMyPageAssignmentStore( $aData );
		}
		return true;
	}

	/**
	 *
	 * @param array &$aData
	 */
	protected static function extendBSApiMyPageAssignmentStore( &$aData ) {
		$aPageIds = [];
		$aDisabledIds = [];
		foreach ( $aData as $oDataSet ) {
			$oTitle = Title::newFromID( $oDataSet->page_id );
			if ( !self::getReadConfirmationMechanismInstance()->mustRead( $oTitle ) ) {
				$aDisabledIds[] = $oDataSet->page_id;
			} else {
				$aPageIds[] = $oDataSet->page_id;
			}
		}

		$iCurrentUserId = RequestContext::getMain()->getUser()->getId();

		$aCurrentPageReads = self::getReadConfirmationMechanismInstance()->getCurrentReadConfirmations(
			[ $iCurrentUserId ],
			$aPageIds
		);

		foreach ( $aData as $oDataSet ) {
			if ( in_array( $oDataSet->page_id, $aDisabledIds ) ) {
				$oDataSet->read_confirmation = 'disabled';
				continue;
			}
			$sTimestamp = null;
			if ( isset( $aCurrentPageReads[$oDataSet->page_id][$iCurrentUserId] ) ) {
				$sTimestamp = $aCurrentPageReads[$oDataSet->page_id][$iCurrentUserId];
			}
			$oDataSet->read_confirmation = $sTimestamp;
		}
	}

	/**
	 *
	 * @param \SpecialPageAssignments|\SpecialManagePageAssignments $oSender
	 * @param array &$aDeps
	 * @return bool
	 */
	public static function onBSPageAssignmentsSpecialPages( $oSender, &$aDeps ) {
		$aDeps[] = 'ext.readconfirmation.pageassignmentsintegration';
		return true;
	}

	/**
	 * @return IMechanism
	 */
	public static function getReadConfirmationMechanismInstance() {
		return MediaWikiServices::getInstance()
			->getService( 'BSReadConfirmationMechanismFactory' )
			->getMechanismInstance();
	}

}
