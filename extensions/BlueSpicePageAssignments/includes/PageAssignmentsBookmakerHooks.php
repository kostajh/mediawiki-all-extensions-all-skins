<?php

use MediaWiki\MediaWikiServices;

class PageAssignmentsBookmakerHooks {

	/**
	 * Adds dependencies to SpecialBookshelfBookManager
	 * @param SpecialBookshelfBookManager $oSender
	 * @param OutputPage $oOutput
	 * @param stdClass $oConfig
	 * @return bool
	 */
	public static function onBSBookshelfBookManager( $oSender, $oOutput, $oConfig ) {
		$oConfig->dependencies[] = 'ext.bluespice.pageassignments.bookshelfPlugin';
		return true;
	}

	/**
	 * Adds information about assignments to PDF export
	 * @param Title $oTitle
	 * @param DOMDocument $oPageDOM
	 * @param array &$aParams
	 * @param DOMXPath $oDOMXPath
	 * @param array &$aMeta
	 * @return bool
	 */
	public static function onBSUEModulePDFcollectMetaData( $oTitle, $oPageDOM,
		&$aParams, $oDOMXPath, &$aMeta ) {
		$aMeta['assigned_users'] = '';
		$aMeta['assigned_groups'] = '';

		$aAssignedUserNames = [];
		$aAssignedGroupNames = [];

		$target = static::getFactory()->newFromTargetTitle( $oTitle );
		if ( $target instanceof \BlueSpice\PageAssignments\ITarget === false ) {
			return true;
		}

		foreach ( $target->getAssignments() as $assignment ) {
			if ( $assignment->getType() === 'user' ) {
				$aAssignedUserNames[] = $assignment->getText();
			}
			if ( $assignment->getType() === 'group' ) {
				$aAssignedGroupNames[] = $assignment->getText();
			}
		}
		if ( !empty( $aAssignedUserNames ) ) {
			$aMeta['assigned_users'] = implode( ', ', $aAssignedUserNames );
		}
		if ( !empty( $aAssignedGroupNames ) ) {
			$aMeta['assigned_groups'] = implode( ', ', $aAssignedGroupNames );
		}

		return true;
	}

	/**
	 * Adds information about assignments to the Bookshelf BookManager grid
	 * @param Title $oBookTitle
	 * @param stdClass $oBookRow
	 * @return bool
	 */
	public static function onBSBookshelfManagerGetBookDataRow( $oBookTitle, $oBookRow ) {
		$oBookRow->assignments = [];
		$aTexts = [];

		$target = static::getFactory()->newFromTargetTitle( $oBookTitle );
		if ( $target instanceof \BlueSpice\PageAssignments\ITarget === false ) {
			return true;
		}

		foreach ( $target->getAssignments() as $assignment ) {
			$oBookRow->assignments[] = $assignment->toStdClass();
			$aTexts[] = $assignment->getText();
		}
		$oBookRow->flat_assignments = implode( '', $aTexts );
		return true;
	}

	/**
	 *
	 * @return BlueSpice\PageAssignments\AssignmentFactory
	 */
	private static function getFactory() {
		return MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
	}
}
