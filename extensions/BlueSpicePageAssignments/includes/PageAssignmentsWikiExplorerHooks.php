<?php

use MediaWiki\MediaWikiServices;

class PageAssignmentsWikiExplorerHooks {

	/**
	 *
	 * @param array &$aFields
	 * @return bool
	 */
	public static function onWikiExplorerGetFieldDefinitions( &$aFields ) {
		$aFields[] = [
			'name' => 'page_assignments',
		];
		return true;
	}

	/**
	 *
	 * @param array &$aColumns
	 * @return bool
	 */
	public static function onWikiExplorerGetColumnDefinitions( &$aColumns ) {
		$aColumns[] = [
			'header' => wfMessage( 'pageassignments' )->escaped(),
			'dataIndex' => 'page_assignments',
			'id' => 'page_assignments',
			'filter' => [
				'type' => 'string'
			],
			'width' => 200,
			'hidden' => true
		];
		return true;
	}

	/**
	 *
	 * @param array $aFilters
	 * @param array &$aTables
	 * @param array &$aFields
	 * @param array &$aConditions
	 * @param array &$aJoinConditions
	 * @return bool
	 */
	public static function onWikiExplorerQueryPagesWithFilter( $aFilters, &$aTables,
		&$aFields, &$aConditions, &$aJoinConditions ) {
		$dbr = wfGetDB( DB_REPLICA );
		$sTablePrefix = $dbr->tablePrefix();

		$aTables[] = "{$sTablePrefix}bs_pageassignments AS assigned";
		$aJoinConditions["{$sTablePrefix}bs_pageassignments AS assigned"] = [
			'LEFT OUTER JOIN',
			"{$sTablePrefix}page.page_id=assigned.pa_page_id"
		];

		$aTables[] = "{$sTablePrefix}user AS page_assignments";
		$aJoinConditions["{$sTablePrefix}user AS page_assignments"] = [
			'LEFT OUTER JOIN',
			"assigned.pa_assignee_key=page_assignments.user_name"
		];
		$aFields[] =
			"GROUP_CONCAT("
				. "IF("
					. "STRCMP(page_assignments.user_real_name,''),"
					. "page_assignments.user_real_name,assigned.pa_assignee_key"
				. ")"
			. ") AS page_assignments";
		$aFields[] = "assigned.pa_assignee_key";

		if ( array_key_exists( 'page_assignments', $aFilters ) ) {
			WikiExplorer::filterStringsTable(
				"CONCAT_WS("
					. "',',"
					. "IF("
						. "STRCMP(page_assignments.user_real_name,''),"
						. "page_assignments.user_real_name,assigned.pa_assignee_key"
					. ")"
				. ")",
				$aFilters['page_assignments'],
				$aConditions
			);
		}

		return true;
	}

	/**
	 *
	 * @param array &$aRows
	 * @return bool
	 */
	public static function onWikiExplorerBuildDataSets( &$aRows ) {
		if ( !count( $aRows ) ) {
			return true;
		}

		foreach ( $aRows as $iKey => $aRowSet ) {
			$aRows[$iKey]['page_assignments'] = '';
		}

		$aPageIds = array_keys( $aRows );

		$dbr = wfGetDB( DB_REPLICA );
		$aTables = [
			'bs_pageassignments'
		];
		$sField = "pa_page_id, pa_position, pa_assignee_type, pa_assignee_key";
		$sCondition = "pa_page_id IN (" . implode( ',', $aPageIds ) . ")";
		$aOptions = [
			'ORDER BY' => 'pa_page_id, pa_position'
		];

		$oRes = $dbr->select(
			$aTables,
			$sField,
			$sCondition,
			__METHOD__,
			$aOptions
		);

		$aData = [];
		$aUserIds = [];
		$aGroups = [];
		$util = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' );

		foreach ( $oRes as $oRow ) {
			if ( $oRow->pa_assignee_type == 'group' ) {
				$aGroups[$oRow->pa_page_id] = $oRow->pa_assignee_key;
				$aData[$oRow->pa_page_id][] =
					'<li>' .
						'<a class="bs-pa-wikiexplorer-groups" href="#">' .
							$oRow->pa_assignee_key .
						'</a>' .
					'</li>';
				continue;
			}
			$oUser = User::newFromName( $oRow->pa_assignee_key );
			if ( !$oUser || $oUser->isAnon() ) {
				continue;
			}
			$aUserIds[$oRow->pa_page_id][] = $oUser->getId();
			$aData[$oRow->pa_page_id][] =
				'<li>' .
					'<a class="bs-pa-wikiexplorer-users" href="#">' .
						$util->getUserHelper( $oUser )->getDisplayName() .
					'</a>' .
				'</li>';
		}

		foreach ( $aRows as $iKey => $aRowSet ) {
			if ( array_key_exists( $iKey, $aData ) ) {
				$aRows[$iKey]['page_assignments'] = Html::rawElement(
					'ul',
					[
						'data-articleId' => $iKey,
						'data-assignees' => FormatJson::encode(
							$aUserIds[$iKey]
						)
					],
					implode( '', $aData[$iKey] )
				);
			}
		}

		return true;
	}
}
