<?php

class ApiExpiryStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $mainModule
	 * @param string $moduleName
	 * @param string $modulePrefix
	 */
	public function __construct( $mainModule, $moduleName, $modulePrefix = '' ) {
		parent::__construct( $mainModule, $moduleName, $modulePrefix );
	}

	/**
	 *
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return [];
		}

		$iOffset = 0;
		$iLimit = 25;
		$sSortField = 'exp_date';
		$sSortDirection = 'DESC';
		$iDate = 0;

		$aData = [
			'results' => [],
			'total' => 0
		];

		$dbr = wfGetDB( DB_REPLICA );
		$sTblPrfx = $dbr->tablePrefix();

		switch ( $sSortField ) {
			case 'rem_date':
				$sSortField = "{$sTblPrfx}bs_expiry.exp_date";
				break;
			case 'page_title':
				$sSortField = "{$sTblPrfx}page.page_title";
				break;
		}

		$aTables = [
			'bs_expiry', 'page'
		];
		$aFields = [
			"{$sTblPrfx}bs_expiry.exp_id",
			"{$sTblPrfx}bs_expiry.exp_page_id",
			"{$sTblPrfx}bs_expiry.exp_date",
			"{$sTblPrfx}page.page_title",
			"{$sTblPrfx}bs_expiry.exp_comment",
		];
		$aConditions = [];
		$aOptions = [
			'ORDER BY' => "{$sSortField} {$sSortDirection}",
			'GROUP BY' => "{$sTblPrfx}bs_expiry.exp_id"
		];

		if ( $iOffset === null ) {
			$aOptions['OFFSET'] = $iOffset;
		}

		if ( $iLimit === null ) {
			$aOptions['LIMIT'] = $iLimit;
		}

		$aJoinConditions = [
			"page" => [ 'JOIN', "{$sTblPrfx}bs_expiry.exp_page_id = {$sTblPrfx}page.page_id" ]
		];

		// give other extensions the opportunity to modify the query
		$this->getServices()->getHookContainer()->run(
			'BsExpiryBeforeBuildOverviewQuery',
			[
				$this,
				&$aTables,
				&$aFields,
				&$aConditions,
				&$aOptions,
				&$aJoinConditions,
				&$sSortField,
				&$sSortDirection
			]
		);

		/*
		$iUserId = $oUser->getId();
		if ( $iUserId && !$oUser->getOption( "MW::Reminder::ShowAllReminders" ) ) {
			$aConditions["{$sTblPrfx}bs_reminder.rem_user_id"] = $iUserId;
		}
		*/
		if ( $iDate !== 0 ) {
			$aConditions[] = "{$sTblPrfx}bs_expiry.exp_date <= '" . $iDate . "'";
		}

		$res = $dbr->select(
			$aTables, $aFields, $aConditions, __METHOD__, $aOptions, $aJoinConditions
		);

		if ( $res ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->exp_page_id );
				$pm = \MediaWiki\MediaWikiServices::getInstance()
					->getPermissionManager();

				if ( !$pm->userCan( 'read', $this->getUser(), $oTitle ) ) {
					continue;
				}
				$canExpire = $pm->userCan( 'expirearticle', $this->getUser(), $oTitle );
				$canDelete = $pm->userCan( 'expiry-delete', $this->getUser(), $oTitle );
				$aResultSet = [
					'id' => $row->exp_id,
					'page_title' => $oTitle->getPrefixedText(),
					'page_link' => $oTitle->getLocalURL(),
					'expiry_date' => $row->exp_date,
					'article_id' => $row->exp_page_id,
					'exp_comment' => $row->exp_comment,
					'user_can_expire' => $canExpire,
					'user_can_delete_expiration' => $canDelete,
				];
				$this->getServices()->getHookContainer()->run( 'BsExpiryBuildOverviewResultSet', [
					$this,
					&$aResultSet,
					$row
				] );
				$aData['results'][] = $aResultSet;
			}
		}

		unset( $aOptions['LIMIT'], $aOptions['OFFSET'] );
		$res = $dbr->select(
			$aTables,
			"COUNT({$sTblPrfx}bs_expiry.exp_id) AS total",
			$aConditions,
			__METHOD__,
			[],
			$aJoinConditions
		);
		if ( $res ) {
			$row = $res->fetchRow();
			$aData['total'] = $row['total'];
		}

		$aReminders = $aData;

		$aOutput = [];

		foreach ( $aReminders['results'] as $aReminder ) {
			$oReminder = (object)$aReminder;
			$aOutput[] = $oReminder;
		}

		return $aOutput;
	}

	/**
	 *
	 * @return bool
	 */
	public function isReadMode() {
		return true;
	}

	/**
	 *
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeMetaData( $sQuery = '' ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isAnon() ) {
			return [];
		}

		$aMetadata = [
			'idProperty' => 'id',
			'root' => 'results',
			'totalProperty' => 'total',
			'successProperty' => 'success',
			'fields' => [
				[ 'name' => 'page_title' ],
				[ 'name' => 'page_link' ],
				[ 'name' => 'exp_date' ],
				[ 'name' => 'article_id' ],
				[ 'name' => 'exp_comment' ]
			],
			'sortInfo' => [
				'field' => 'exp_date',
				'direction' => 'DESC'
			]
		];

		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-expiry-header-pagename' )->plain(),
			'dataIndex' => 'page_title',
			'render' => 'page',
			'sortable' => true
		];
		$aMetadata['columns'][] = [
			'header' => wfMessage( 'bs-expiry-header-date' )->plain(),
			'dataIndex' => 'exp_date',
			'render' => 'date',
			'sortable' => true
		];

		$this->getServices()->getHookContainer()->run( 'BsExpiryBuildOverviewMetadata', [
			&$aMetadata
		] );

		return $aMetadata;
	}

	/**
	 *
	 * @return string
	 */
	protected function getDescription() {
		return 'ExtJS store backend for Expiry';
	}

}
