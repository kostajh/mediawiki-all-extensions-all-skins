<?php

use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\AbuseFilter\AbuseFilterPermissionManager;
use MediaWiki\Extension\AbuseFilter\AbuseFilterServices;
use MediaWiki\Extension\AbuseFilter\CentralDBNotAvailableException;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;

class SpecialAbuseLog extends AbuseFilterSpecialPage {
	/**
	 * @var User The user whose AbuseLog entries are being searched
	 */
	protected $mSearchUser;

	/**
	 * @var string The start time of the search period
	 */
	protected $mSearchPeriodStart;

	/**
	 * @var string The end time of the search period
	 */
	protected $mSearchPeriodEnd;

	/**
	 * @var Title The page of which AbuseLog entries are being searched
	 */
	protected $mSearchTitle;

	/**
	 * @var string The action performed by the user
	 */
	protected $mSearchAction;

	/**
	 * @var string The action taken by AbuseFilter
	 */
	protected $mSearchActionTaken;

	/**
	 * @var string The wiki name where we're performing the search
	 */
	protected $mSearchWiki;

	/**
	 * @var string|null The filter IDs we're looking for. Either a single one, or a pipe-separated list
	 */
	protected $mSearchFilter;

	/**
	 * @var string The visibility of entries we're interested in
	 */
	protected $mSearchEntries;

	/**
	 * @var string The impact of the user action, i.e. if the change has been saved
	 */
	protected $mSearchImpact;

	/** @var string|null The filter group to search, as defined in $wgAbuseFilterValidGroups */
	protected $mSearchGroup;

	/** @var LinkBatchFactory */
	private $linkBatchFactory;

	/** @var PermissionManager */
	private $permissionManager;

	/** @var AbuseFilterPermissionManager */
	private $afPermissionManager;

	/**
	 * @param LinkBatchFactory $linkBatchFactory
	 * @param PermissionManager $permissionManager
	 * @param AbuseFilterPermissionManager $afPermissionManager
	 */
	public function __construct(
		LinkBatchFactory $linkBatchFactory,
		PermissionManager $permissionManager,
		AbuseFilterPermissionManager $afPermissionManager
	) {
		parent::__construct( 'AbuseLog', 'abusefilter-log' );
		$this->linkBatchFactory = $linkBatchFactory;
		$this->permissionManager = $permissionManager;
		$this->afPermissionManager = $afPermissionManager;
	}

	/**
	 * @inheritDoc
	 */
	public function doesWrites() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getGroupName() {
		return 'changes';
	}

	/**
	 * Main routine
	 *
	 * $parameter string is converted into the $args array, which can come in
	 * three shapes:
	 *
	 * An array of size 2: only if the URL is like Special:AbuseLog/private/id
	 * where id is the log identifier. In this case, the private details of the
	 * log (e.g. IP address) will be shown.
	 *
	 * An array of size 1: either the URL is like Special:AbuseLog/id where
	 * the id is log identifier, in which case the details of the log except for
	 * private bits (e.g. IP address) are shown, or the URL is incomplete as in
	 * Special:AbuseLog/private (without speciying id), in which case a warning
	 * is shown to the user
	 *
	 * An array of size 0 when URL is like Special:AbuseLog or an array of size
	 * 1 when the URL is like Special:AbuseFilter/ (i.e. without anything after
	 * the slash). In this case, if the parameter `hide` was passed, it will be
	 * used as the identifier of the log entry that we want to hide; otherwise,
	 * the abuse logs are shown as a list, with a search form above the list.
	 *
	 * @param string|null $parameter URL parameters
	 */
	public function execute( $parameter ) {
		$out = $this->getOutput();
		$request = $this->getRequest();

		$this->addNavigationLinks( 'log' );

		$this->setHeaders();
		$this->addHelpLink( 'Extension:AbuseFilter' );
		$this->loadParameters();

		$out->enableClientCache( false );

		$out->addModuleStyles( 'ext.abuseFilter' );

		$this->checkPermissions();

		$hideid = $request->getIntOrNull( 'hide' );
		$args = explode( '/', $parameter );

		if ( count( $args ) === 2 && $args[0] === 'private' ) {
			$this->showPrivateDetails( (int)$args[1] );
		} elseif ( count( $args ) === 1 && $args[0] !== '' ) {
			if ( $args[0] === 'private' ) {
				$out->addWikiMsg( 'abusefilter-invalid-request-noid' );
			} else {
				$this->showDetails( $args[0] );
			}
		} elseif ( $hideid ) {
			$this->showHideForm( $hideid );
		} else {
			$this->searchForm();
			$this->showList();
		}
	}

	/**
	 * Loads parameters from request
	 */
	public function loadParameters() {
		$request = $this->getRequest();

		$searchUsername = trim( $request->getText( 'wpSearchUser' ) );
		$userTitle = Title::newFromText( $searchUsername, NS_USER );
		$this->mSearchUser = $userTitle ? $userTitle->getText() : null;
		if ( $this->getConfig()->get( 'AbuseFilterIsCentral' ) ) {
			$this->mSearchWiki = $request->getText( 'wpSearchWiki' );
		}

		$this->mSearchPeriodStart = $request->getText( 'wpSearchPeriodStart' );
		$this->mSearchPeriodEnd = $request->getText( 'wpSearchPeriodEnd' );
		$this->mSearchTitle = $request->getText( 'wpSearchTitle' );

		$this->mSearchFilter = null;
		$this->mSearchGroup = null;
		if ( $this->afPermissionManager->canSeeLogDetails( $this->getUser() ) ) {
			$this->mSearchFilter = $request->getText( 'wpSearchFilter' );
			if ( count( $this->getConfig()->get( 'AbuseFilterValidGroups' ) ) > 1 ) {
				$this->mSearchGroup = $request->getText( 'wpSearchGroup' );
			}
		}

		$this->mSearchAction = $request->getText( 'wpSearchAction' );
		$this->mSearchActionTaken = $request->getText( 'wpSearchActionTaken' );
		$this->mSearchEntries = $request->getText( 'wpSearchEntries' );
		$this->mSearchImpact = $request->getText( 'wpSearchImpact' );
	}

	/**
	 * @return string[]
	 */
	private function getAllActions() {
		$config = $this->getConfig();
		return array_unique(
			array_merge(
				array_keys( $config->get( 'AbuseFilterActions' ) ),
				array_keys( $config->get( 'AbuseFilterCustomActionsHandlers' ) )
			)
		);
	}

	/**
	 * @return string[]
	 */
	private function getAllFilterableActions() {
		return [
			'edit',
			'move',
			'upload',
			'stashupload',
			'delete',
			'createaccount',
			'autocreateaccount',
		];
	}

	/**
	 * Builds the search form
	 */
	public function searchForm() {
		$user = $this->getUser();
		$formDescriptor = [
			'SearchUser' => [
				'label-message' => 'abusefilter-log-search-user',
				'type' => 'user',
				'ipallowed' => true,
				'default' => $this->mSearchUser,
			],
			'SearchPeriodStart' => [
				'label-message' => 'abusefilter-test-period-start',
				'type' => 'datetime',
				'default' => $this->mSearchPeriodStart
			],
			'SearchPeriodEnd' => [
				'label-message' => 'abusefilter-test-period-end',
				'type' => 'datetime',
				'default' => $this->mSearchPeriodEnd
			],
			'SearchTitle' => [
				'label-message' => 'abusefilter-log-search-title',
				'type' => 'title',
				'default' => $this->mSearchTitle,
				'required' => false
			],
			'SearchImpact' => [
				'label-message' => 'abusefilter-log-search-impact',
				'type' => 'select',
				'options' => [
					$this->msg( 'abusefilter-log-search-impact-all' )->text() => 0,
					$this->msg( 'abusefilter-log-search-impact-saved' )->text() => 1,
					$this->msg( 'abusefilter-log-search-impact-not-saved' )->text() => 2,
				],
			],
		];
		$filterableActions = $this->getAllFilterableActions();
		$actions = array_combine( $filterableActions, $filterableActions );
		ksort( $actions );
		$actions = array_merge(
			[ $this->msg( 'abusefilter-log-search-action-any' )->text() => 'any' ],
			$actions,
			[ $this->msg( 'abusefilter-log-search-action-other' )->text() => 'other' ]
		);
		$formDescriptor['SearchAction'] = [
			'label-message' => 'abusefilter-log-search-action-label',
			'type' => 'select',
			'options' => $actions,
			'default' => 'any',
		];
		$options = [];
		$context = $this->getContext();
		foreach ( $this->getAllActions() as $action ) {
			$key = AbuseFilter::getActionDisplay( $action, $context );
			$options[$key] = $action;
		}
		ksort( $options );
		$options = array_merge(
			[ $this->msg( 'abusefilter-log-search-action-taken-any' )->text() => '' ],
			$options,
			[ $this->msg( 'abusefilter-log-noactions-filter' )->text() => 'noactions' ]
		);
		$formDescriptor['SearchActionTaken'] = [
			'label-message' => 'abusefilter-log-search-action-taken-label',
			'type' => 'select',
			'options' => $options,
		];
		if ( $this->afPermissionManager->canSeeHiddenLogEntries( $user ) ) {
			$formDescriptor['SearchEntries'] = [
				'type' => 'select',
				'label-message' => 'abusefilter-log-search-entries-label',
				'options' => [
					$this->msg( 'abusefilter-log-search-entries-all' )->text() => 0,
					$this->msg( 'abusefilter-log-search-entries-hidden' )->text() => 1,
					$this->msg( 'abusefilter-log-search-entries-visible' )->text() => 2,
				],
			];
		}

		if ( $this->afPermissionManager->canSeeLogDetails( $user ) ) {
			$groups = $this->getConfig()->get( 'AbuseFilterValidGroups' );
			if ( count( $groups ) > 1 ) {
				$options = array_merge(
					[ $this->msg( 'abusefilter-log-search-group-any' )->text() => 0 ],
					array_combine( $groups, $groups )
				);
				$formDescriptor['SearchGroup'] = [
					'label-message' => 'abusefilter-log-search-group',
					'type' => 'select',
					'options' => $options
				];
			}
			$helpmsg = $this->getConfig()->get( 'AbuseFilterIsCentral' )
				? $this->msg( 'abusefilter-log-search-filter-help-central' )->escaped()
				: $this->msg( 'abusefilter-log-search-filter-help' )
					->params( AbuseFilter::GLOBAL_FILTER_PREFIX )->escaped();
			$formDescriptor['SearchFilter'] = [
				'label-message' => 'abusefilter-log-search-filter',
				'type' => 'text',
				'default' => $this->mSearchFilter,
				'help' => $helpmsg
			];
		}
		if ( $this->getConfig()->get( 'AbuseFilterIsCentral' ) ) {
			// @todo Add free form input for wiki name. Would be nice to generate
			// a select with unique names in the db at some point.
			$formDescriptor['SearchWiki'] = [
				'label-message' => 'abusefilter-log-search-wiki',
				'type' => 'text',
				'default' => $this->mSearchWiki,
			];
		}

		HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() )
			->setWrapperLegendMsg( 'abusefilter-log-search' )
			->setSubmitTextMsg( 'abusefilter-log-search-submit' )
			->setMethod( 'get' )
			->setCollapsibleOptions( true )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * @param int $id
	 */
	public function showHideForm( $id ) {
		$output = $this->getOutput();
		if ( !$this->afPermissionManager->canHideAbuseLog( $this->getUser() ) ) {
			$output->addWikiMsg( 'abusefilter-log-hide-forbidden' );

			return;
		}

		$dbr = wfGetDB( DB_REPLICA );

		$deleted = $dbr->selectField(
			'abuse_filter_log',
			'afl_deleted',
			[ 'afl_id' => $id ],
			__METHOD__
		);

		if ( $deleted === false ) {
			$output->addWikiMsg( 'abusefilter-log-nonexistent' );
			return;
		}

		$hideReasonsOther = $this->msg( 'revdelete-reasonotherlist' )->text();
		$hideReasons = $this->msg( 'revdelete-reason-dropdown-suppress' )->inContentLanguage()->text();
		$hideReasons = Xml::listDropDownOptions( $hideReasons, [ 'other' => $hideReasonsOther ] );

		$formInfo = [
			'showorhide' => [
				'type' => 'radio',
				'label-message' => 'abusefilter-log-hide-set-visibility',
				'options-messages' => [
					'abusefilter-log-hide-show' => 'show',
					'abusefilter-log-hide-hide' => 'hide'
				],
				'default' => (int)$deleted === 0 ? 'show' : 'hide',
				'flatlist' => true
			],
			'logid' => [
				'type' => 'info',
				'default' => (string)$id,
				'label-message' => 'abusefilter-log-hide-id',
			],
			'dropdownreason' => [
				'type' => 'select',
				'options' => $hideReasons,
				'label-message' => 'abusefilter-log-hide-reason'
			],
			'reason' => [
				'type' => 'text',
				'label-message' => 'abusefilter-log-hide-reason-other',
			],
		];

		HTMLForm::factory( 'ooui', $formInfo, $this->getContext() )
			->setTitle( $this->getPageTitle() )
			->setWrapperLegend( $this->msg( 'abusefilter-log-hide-legend' )->text() )
			->addHiddenField( 'hide', $id )
			->setSubmitCallback( [ $this, 'saveHideForm' ] )
			->show();

		// Show suppress log for this entry
		$suppressLogPage = new LogPage( 'suppress' );
		$output->addHTML( "<h2>" . $suppressLogPage->getName()->escaped() . "</h2>\n" );
		LogEventsList::showLogExtract( $output, 'suppress', $this->getPageTitle( (string)$id ) );
	}

	/**
	 * @param array $fields
	 * @return bool
	 */
	public function saveHideForm( $fields ) {
		$logid = $this->getRequest()->getVal( 'hide' );

		$newValue = $fields['showorhide'] === 'hide' ? 1 : 0;
		$dbw = wfGetDB( DB_MASTER );

		$dbw->update(
			'abuse_filter_log',
			[ 'afl_deleted' => $newValue ],
			[ 'afl_id' => $logid ],
			__METHOD__
		);

		$reason = $fields['dropdownreason'];
		if ( $reason === 'other' ) {
			$reason = $fields['reason'];
		} elseif ( $fields['reason'] !== '' ) {
			$reason .=
				$this->msg( 'colon-separator' )->inContentLanguage()->text() . $fields['reason'];
		}

		$action = $fields['showorhide'] === 'hide' ? 'hide-afl' : 'unhide-afl';
		$logEntry = new ManualLogEntry( 'suppress', $action );
		$logEntry->setPerformer( $this->getUser() );
		$logEntry->setTarget( $this->getPageTitle( $logid ) );
		$logEntry->setComment( $reason );
		$logEntry->insert();

		$this->getOutput()->redirect( SpecialPage::getTitleFor( 'AbuseLog' )->getFullURL() );

		return true;
	}

	/**
	 * Shows the results list
	 */
	public function showList() {
		$out = $this->getOutput();
		$user = $this->getUser();
		$this->outputHeader( 'abusefilter-log-summary' );

		// Generate conditions list.
		$conds = [];

		if ( $this->mSearchUser ) {
			$searchedUser = User::newFromName( $this->mSearchUser );

			if ( !$searchedUser ) {
				$conds['afl_user'] = 0;
				$conds['afl_user_text'] = $this->mSearchUser;
			} else {
				$conds['afl_user'] = $searchedUser->getId();
				$conds['afl_user_text'] = $searchedUser->getName();
			}
		}

		$dbr = wfGetDB( DB_REPLICA );
		if ( $this->mSearchPeriodStart ) {
			$conds[] = 'afl_timestamp >= ' .
				$dbr->addQuotes( $dbr->timestamp( strtotime( $this->mSearchPeriodStart ) ) );
		}

		if ( $this->mSearchPeriodEnd ) {
			$conds[] = 'afl_timestamp <= ' .
				$dbr->addQuotes( $dbr->timestamp( strtotime( $this->mSearchPeriodEnd ) ) );
		}

		if ( $this->mSearchWiki ) {
			if ( $this->mSearchWiki === WikiMap::getCurrentWikiDbDomain()->getId() ) {
				$conds['afl_wiki'] = null;
			} else {
				$conds['afl_wiki'] = $this->mSearchWiki;
			}
		}

		$groupFilters = [];
		if ( $this->mSearchGroup ) {
			$groupFilters = $dbr->selectFieldValues(
				'abuse_filter',
				'af_id',
				[ 'af_group' => $this->mSearchGroup ],
				__METHOD__
			);
		}

		$searchFilters = [];
		if ( $this->mSearchFilter ) {
			$rawFilters = array_map( 'trim', explode( '|', $this->mSearchFilter ) );
			// Map of [ [ id, global ], ... ]
			$filtersList = [];
			$foundInvalid = false;
			foreach ( $rawFilters as $filter ) {
				try {
					$filtersList[] = AbuseFilter::splitGlobalName( $filter );
				} catch ( InvalidArgumentException $e ) {
					$foundInvalid = true;
					continue;
				}
			}

			// @phan-suppress-next-line PhanImpossibleCondition
			if ( $foundInvalid ) {
				$out->addHTML(
					Html::rawElement(
						'p',
						[],
						Html::warningBox( $this->msg( 'abusefilter-log-invalid-filter' )->escaped() )
					)
				);
			}

			// if a filter is hidden, users who can't view private filters should
			// not be able to find log entries generated by it.
			if ( !$this->afPermissionManager->canViewPrivateFiltersLogs( $user ) ) {
				$searchedForPrivate = false;
				foreach ( $filtersList as $index => $filterData ) {
					if ( AbuseFilterServices::getFilterLookup()->getFilter( ...$filterData )->isHidden() ) {
						unset( $filtersList[$index] );
						$searchedForPrivate = true;
					}
				}
				if ( $searchedForPrivate ) {
					$out->addWikiMsg( 'abusefilter-log-private-not-included' );
				}
			}

			foreach ( $filtersList as $filterData ) {
				$searchFilters[] = AbuseFilter::buildGlobalName( ...$filterData );
			}
		}

		$searchIDs = null;
		if ( $this->mSearchGroup && !$this->mSearchFilter ) {
			$searchIDs = $groupFilters;
		} elseif ( !$this->mSearchGroup && $this->mSearchFilter ) {
			$searchIDs = $searchFilters;
		} elseif ( $this->mSearchGroup && $this->mSearchFilter ) {
			$searchIDs = array_intersect( $groupFilters, $searchFilters );
		}

		if ( $searchIDs !== null ) {
			if ( !count( $searchIDs ) ) {
				$out->addWikiMsg( 'abusefilter-log-noresults' );
				return;
			}

			$conds['afl_filter'] = $searchIDs;
		}

		$searchTitle = Title::newFromText( $this->mSearchTitle );
		if ( $this->mSearchTitle && $searchTitle ) {
			$conds['afl_namespace'] = $searchTitle->getNamespace();
			$conds['afl_title'] = $searchTitle->getDBkey();
		}

		if ( $this->afPermissionManager->canSeeHiddenLogEntries( $user ) ) {
			if ( $this->mSearchEntries === '1' ) {
				$conds['afl_deleted'] = 1;
			} elseif ( $this->mSearchEntries === '2' ) {
				$conds['afl_deleted'] = 0;
			}
		}

		if ( in_array( $this->mSearchImpact, [ '1', '2' ] ) ) {
			$unsuccessfulActionConds = 'afl_rev_id IS NULL';
			if ( $this->mSearchImpact === '1' ) {
				$conds[] = "NOT ( $unsuccessfulActionConds )";
			} else {
				$conds[] = $unsuccessfulActionConds;
			}
		}

		if ( $this->mSearchActionTaken ) {
			if ( in_array( $this->mSearchActionTaken, $this->getAllActions() ) ) {
				$list = [ 'afl_actions' => $this->mSearchActionTaken ];
				$list[] = 'afl_actions' . $dbr->buildLike(
					$this->mSearchActionTaken, ',', $dbr->anyString() );
				$list[] = 'afl_actions' . $dbr->buildLike(
					$dbr->anyString(), ',', $this->mSearchActionTaken );
				$list[] = 'afl_actions' . $dbr->buildLike(
					$dbr->anyString(),
					',', $this->mSearchActionTaken, ',',
					$dbr->anyString()
				);
				$conds[] = $dbr->makeList( $list, LIST_OR );
			} elseif ( $this->mSearchActionTaken === 'noactions' ) {
				$conds['afl_actions'] = '';
			}
		}

		if ( $this->mSearchAction ) {
			$filterableActions = $this->getAllFilterableActions();
			if ( in_array( $this->mSearchAction, $filterableActions ) ) {
				$conds['afl_action'] = $this->mSearchAction;
			} elseif ( $this->mSearchAction === 'other' ) {
				$list = $dbr->makeList( [ 'afl_action' => $filterableActions ], LIST_OR );
				$conds[] = "NOT ( $list )";
			}
		}

		$pager = new AbuseLogPager(
			$this,
			$conds,
			$this->linkBatchFactory,
			$this->canSeeUndeleteDiffs()
		);
		$pager->doQuery();
		$result = $pager->getResult();

		$form = Xml::tags(
			'form',
			[
				'method' => 'GET',
				'action' => $this->getPageTitle()->getLocalURL()
			],
			Xml::tags( 'ul', [ 'class' => 'plainlinks' ], $pager->getBody() )
		);

		if ( $result && $result->numRows() !== 0 ) {
			$out->addHTML( $pager->getNavigationBar() . $form . $pager->getNavigationBar() );
		} else {
			$out->addWikiMsg( 'abusefilter-log-noresults' );
		}
	}

	/**
	 * @param string|int $id
	 * @suppress SecurityCheck-SQLInjection
	 */
	public function showDetails( $id ) {
		$out = $this->getOutput();
		$user = $this->getUser();

		$pager = new AbuseLogPager(
			$this,
			[],
			$this->linkBatchFactory,
			$this->canSeeUndeleteDiffs()
		);

		[
			'tables' => $tables,
			'fields' => $fields,
			'join_conds' => $join_conds,
		] = $pager->getQueryInfo();

		$dbr = wfGetDB( DB_REPLICA );
		$row = $dbr->selectRow(
			$tables,
			$fields,
			[ 'afl_id' => $id ],
			__METHOD__,
			[],
			$join_conds
		);

		$error = null;
		if ( !$row ) {
			$error = 'abusefilter-log-nonexistent';
		} else {
			list( $filterID, $global ) = AbuseFilter::splitGlobalName( $row->afl_filter );
			if ( $global ) {
				$filter_hidden = AbuseFilterServices::getFilterLookup()->getFilter( $filterID, $global )->isHidden();
			} else {
				$filter_hidden = $row->af_hidden;
			}

			if ( !$this->afPermissionManager->canSeeLogDetailsForFilter( $user, $filter_hidden ) ) {
				$error = 'abusefilter-log-cannot-see-details';
			} elseif (
				self::isHidden( $row ) === true &&
				!$this->afPermissionManager->canSeeHiddenLogEntries( $user )
			) {
				$error = 'abusefilter-log-details-hidden';
			} elseif ( self::isHidden( $row ) === 'implicit' ) {
				$revRec = MediaWikiServices::getInstance()
					->getRevisionLookup()
					->getRevisionById( (int)$row->afl_rev_id );
				if ( !AbuseFilter::userCanViewRev( $revRec, $user ) ) {
					// The log is visible, but refers to a deleted revision
					$error = 'abusefilter-log-details-hidden-implicit';
				}
			}
		}

		if ( $error ) {
			$out->addWikiMsg( $error );
			return;
		}

		$output = Xml::element(
			'legend',
			null,
			$this->msg( 'abusefilter-log-details-legend' )
				->numParams( $id )
				->text()
		);
		$output .= Xml::tags( 'p', null, $this->formatRow( $row, false ) );

		// Load data
		$vars = AbuseFilter::loadVarDump( $row->afl_var_dump );
		$out->addJsConfigVars( 'wgAbuseFilterVariables', $vars->dumpAllVars( true ) );

		// Diff, if available
		if ( $row->afl_action === 'edit' ) {
			$vars->setLogger( LoggerFactory::getInstance( 'AbuseFilter' ) );
			// GET_BC because these variables may be unset in case of data corruption (T264513)
			$old_wikitext = $vars->getVar( 'old_wikitext', AbuseFilterVariableHolder::GET_BC )->toString();
			$new_wikitext = $vars->getVar( 'new_wikitext', AbuseFilterVariableHolder::GET_BC )->toString();

			$diffEngine = new DifferenceEngine( $this->getContext() );

			$diffEngine->showDiffStyle();

			$formattedDiff = $diffEngine->addHeader(
				$diffEngine->generateTextDiffBody( $old_wikitext, $new_wikitext ),
				'', ''
			);

			$output .=
				Xml::tags(
					'h3',
					null,
					$this->msg( 'abusefilter-log-details-diff' )->parse()
				);

			$output .= $formattedDiff;
		}

		$output .= Xml::element( 'h3', null, $this->msg( 'abusefilter-log-details-vars' )->text() );

		// Build a table.
		$output .= AbuseFilter::buildVarDumpTable( $vars, $this->getContext() );

		if ( $this->afPermissionManager->canSeePrivateDetails( $user ) ) {
			$formDescriptor = [
				'Reason' => [
					'label-message' => 'abusefilter-view-privatedetails-reason',
					'type' => 'text',
					'size' => 45,
				],
			];

			$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
			$htmlForm->setWrapperLegendMsg( 'abusefilter-view-privatedetails-legend' )
				->setAction( $this->getPageTitle( 'private/' . $id )->getLocalURL() )
				->setSubmitTextMsg( 'abusefilter-view-privatedetails-submit' )
				->setMethod( 'post' )
				->prepareForm();

			$output .= $htmlForm->getHTML( false );
		}

		$out->addHTML( Xml::tags( 'fieldset', null, $output ) );
	}

	/**
	 * Can this user see diffs generated by Special:Undelete?
	 * @see \SpecialUndelete
	 *
	 * @return bool
	 */
	private function canSeeUndeleteDiffs() : bool {
		if ( !$this->permissionManager->userHasRight( $this->getUser(), 'deletedhistory' ) ) {
			return false;
		}

		return $this->permissionManager->userHasAnyRight(
			$this->getUser(), 'deletedtext', 'undelete' );
	}

	/**
	 * Can this user see diffs generated by Special:Undelete for the page?
	 * @see \SpecialUndelete
	 * @param LinkTarget $page
	 *
	 * @return bool
	 */
	private function canSeeUndeleteDiffForPage( LinkTarget $page ) : bool {
		if ( !$this->canSeeUndeleteDiffs() ) {
			return false;
		}

		foreach ( [ 'deletedtext', 'undelete' ] as $action ) {
			if ( $this->permissionManager->userCan(
				$action, $this->getUser(), $page, PermissionManager::RIGOR_QUICK
			) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Helper function to select a row with private details and some more context
	 * for an AbuseLog entry.
	 *
	 * @param User $user The user who's trying to view the row
	 * @param int $id The ID of the log entry
	 * @return Status A status object with the requested row stored in the value property,
	 *  or an error and no row.
	 */
	public static function getPrivateDetailsRow( User $user, $id ) {
		$afPermManager = AbuseFilterServices::getPermissionManager();
		$dbr = wfGetDB( DB_REPLICA );
		$row = $dbr->selectRow(
			[ 'abuse_filter_log', 'abuse_filter' ],
			[ 'afl_id', 'afl_filter', 'afl_user_text', 'afl_timestamp', 'afl_ip', 'af_id',
				'af_public_comments', 'af_hidden' ],
			[ 'afl_id' => $id ],
			__METHOD__,
			[],
			[ 'abuse_filter' => [ 'LEFT JOIN', 'af_id=afl_filter' ] ]
		);

		$status = Status::newGood();
		if ( !$row ) {
			$status->fatal( 'abusefilter-log-nonexistent' );
			return $status;
		}

		list( $filterID, $global ) = AbuseFilter::splitGlobalName( $row->afl_filter );
		if ( $global ) {
			$lookup = AbuseFilterServices::getFilterLookup();
			$filterHidden = $lookup->getFilter( $filterID, $global )->isHidden();
		} else {
			$filterHidden = $row->af_hidden;
		}

		if ( !$afPermManager->canSeeLogDetailsForFilter( $user, $filterHidden ) ) {
			$status->fatal( 'abusefilter-log-cannot-see-details' );
			return $status;
		}
		$status->setResult( true, $row );
		return $status;
	}

	/**
	 * Builds an HTML table with the private details for a given abuseLog entry.
	 *
	 * @param stdClass $row The row, as returned by self::getPrivateDetailsRow()
	 * @return string The HTML output
	 */
	protected function buildPrivateDetailsTable( $row ) {
		$output = Xml::element(
			'legend',
			null,
			$this->msg( 'abusefilter-log-details-privatedetails' )->text()
		);

		$header =
			Xml::element( 'th', null, $this->msg( 'abusefilter-log-details-var' )->text() ) .
			Xml::element( 'th', null, $this->msg( 'abusefilter-log-details-val' )->text() );

		$output .=
			Xml::openElement( 'table',
				[
					'class' => 'wikitable mw-abuselog-private',
					'style' => 'width: 80%;'
				]
			) .
			Xml::openElement( 'tbody' );
		$output .= $header;

		// Log ID
		$linkRenderer = $this->getLinkRenderer();
		$output .=
			Xml::tags( 'tr', null,
				Xml::element( 'td',
					[ 'style' => 'width: 30%;' ],
					$this->msg( 'abusefilter-log-details-id' )->text()
				) .
				Xml::openElement( 'td' ) .
				$linkRenderer->makeKnownLink(
					$this->getPageTitle( $row->afl_id ),
					$this->getLanguage()->formatNum( $row->afl_id )
				) .
				Xml::closeElement( 'td' )
			);

		// Timestamp
		$output .=
			Xml::tags( 'tr', null,
				Xml::element( 'td',
					[ 'style' => 'width: 30%;' ],
					$this->msg( 'abusefilter-edit-builder-vars-timestamp-expanded' )->text()
				) .
				Xml::element( 'td',
					null,
					$this->getLanguage()->timeanddate( $row->afl_timestamp, true )
				)
			);

		// User
		$output .=
			Xml::tags( 'tr', null,
				Xml::element( 'td',
					[ 'style' => 'width: 30%;' ],
					$this->msg( 'abusefilter-edit-builder-vars-user-name' )->text()
				) .
				Xml::element( 'td',
					null,
					$row->afl_user_text
				)
			);

		// Filter ID
		$output .=
			Xml::tags( 'tr', null,
				Xml::element( 'td',
					[ 'style' => 'width: 30%;' ],
					$this->msg( 'abusefilter-list-id' )->text()
				) .
				Xml::openElement( 'td' ) .
				$linkRenderer->makeKnownLink(
					SpecialPage::getTitleFor( 'AbuseFilter', $row->af_id ),
					$this->getLanguage()->formatNum( $row->af_id )
				) .
				Xml::closeElement( 'td' )
			);

		// Filter description
		$output .=
			Xml::tags( 'tr', null,
				Xml::element( 'td',
					[ 'style' => 'width: 30%;' ],
					$this->msg( 'abusefilter-list-public' )->text()
				) .
				Xml::element( 'td',
					null,
					$row->af_public_comments
				)
			);

		// IP address
		if ( $row->afl_ip !== '' ) {
			if ( ExtensionRegistry::getInstance()->isLoaded( 'CheckUser' ) &&
				$this->permissionManager->userHasRight( $this->getUser(), 'checkuser' )
			) {
				$CULink = '&nbsp;&middot;&nbsp;' . $linkRenderer->makeKnownLink(
					SpecialPage::getTitleFor(
						'CheckUser',
						$row->afl_ip
					),
					$this->msg( 'abusefilter-log-details-checkuser' )->text()
				);
			} else {
				$CULink = '';
			}
			$output .=
				Xml::tags( 'tr', null,
					Xml::element( 'td',
						[ 'style' => 'width: 30%;' ],
						$this->msg( 'abusefilter-log-details-ip' )->text()
					) .
					Xml::tags(
						'td',
						null,
						self::getUserLinks( 0, $row->afl_ip ) . $CULink
					)
				);
		} else {
			$output .=
				Xml::tags( 'tr', null,
					Xml::element( 'td',
						[ 'style' => 'width: 30%;' ],
						$this->msg( 'abusefilter-log-details-ip' )->text()
					) .
					Xml::element(
						'td',
						null,
						$this->msg( 'abusefilter-log-ip-not-available' )->text()
					)
				);
		}

		$output .= Xml::closeElement( 'tbody' ) . Xml::closeElement( 'table' );

		$output = Xml::tags( 'fieldset', null, $output );
		return $output;
	}

	/**
	 * @param int $id
	 * @return void
	 */
	public function showPrivateDetails( $id ) {
		$out = $this->getOutput();
		$user = $this->getUser();

		if ( !$this->afPermissionManager->canSeePrivateDetails( $user ) ) {
			$out->addWikiMsg( 'abusefilter-log-cannot-see-privatedetails' );

			return;
		}
		$request = $this->getRequest();

		// Make sure it is a valid request
		$token = $request->getVal( 'wpEditToken' );
		if ( !$request->wasPosted() || !$user->matchEditToken( $token ) ) {
			$out->addHTML(
				Xml::tags(
					'p',
					null,
					Html::errorBox( $this->msg( 'abusefilter-invalid-request' )->params( $id )->parse() )
				)
			);

			return;
		}

		$reason = $request->getText( 'wpReason' );
		if ( !self::checkPrivateDetailsAccessReason( $reason ) ) {
			$out->addWikiMsg( 'abusefilter-noreason' );
			$this->showDetails( $id );
			return;
		}

		$status = self::getPrivateDetailsRow( $user, $id );
		if ( !$status->isGood() ) {
			$out->addWikiMsg( $status->getErrors()[0] );
			return;
		}
		$row = $status->getValue();

		// Log accessing private details
		if ( $this->getConfig()->get( 'AbuseFilterLogPrivateDetailsAccess' ) ) {
			self::addPrivateDetailsAccessLogEntry( $id, $reason, $user );
		}

		// Show private details (IP).
		$table = $this->buildPrivateDetailsTable( $row );
		$out->addHTML( $table );
	}

	/**
	 * If specifying a reason for viewing private details of abuse log is required
	 * then it makes sure that a reason is provided.
	 *
	 * @param string $reason
	 * @return bool
	 */
	public static function checkPrivateDetailsAccessReason( $reason ) {
		global $wgAbuseFilterPrivateDetailsForceReason;
		return ( !$wgAbuseFilterPrivateDetailsForceReason || strlen( $reason ) > 0 );
	}

	/**
	 * @param int $logID int The ID of the AbuseFilter log that was accessed
	 * @param string $reason The reason provided for accessing private details
	 * @param User $user The user who accessed the private details
	 * @return void
	 */
	public static function addPrivateDetailsAccessLogEntry( $logID, $reason, User $user ) {
		$target = self::getTitleFor( 'AbuseLog', (string)$logID );

		$logEntry = new ManualLogEntry( 'abusefilterprivatedetails', 'access' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( $target );
		$logEntry->setParameters( [
			'4::logid' => $logID,
		] );
		$logEntry->setComment( $reason );

		$logEntry->insert();
	}

	/**
	 * @param stdClass $row
	 * @param bool $isListItem
	 * @return string
	 */
	public function formatRow( $row, $isListItem = true ) {
		$user = $this->getUser();
		$lang = $this->getLanguage();

		$title = Title::makeTitle( $row->afl_namespace, $row->afl_title );

		$diffLink = false;
		$isHidden = self::isHidden( $row );

		// @todo T224203 Try to show the details if the revision is deleted but the AbuseLog entry
		// is not. However, watch out to avoid showing too much stuff.
		if ( !$this->afPermissionManager->canSeeHiddenLogEntries( $user ) && $isHidden ) {
			return '';
		}

		$linkRenderer = $this->getLinkRenderer();

		if ( !$row->afl_wiki ) {
			$pageLink = $linkRenderer->makeLink(
				$title,
				null,
				[],
				[ 'redirect' => 'no' ]
			);
			if ( $row->rev_id ) {
				$diffLink = $linkRenderer->makeKnownLink(
					$title,
					new HtmlArmor( $this->msg( 'abusefilter-log-diff' )->parse() ),
					[],
					[ 'diff' => 'prev', 'oldid' => $row->rev_id ]
				);
			} elseif (
				isset( $row->ar_timestamp ) && $row->ar_timestamp
				&& $this->canSeeUndeleteDiffForPage( $title )
			) {
				$diffLink = $linkRenderer->makeKnownLink(
					SpecialPage::getTitleFor( 'Undelete' ),
					new HtmlArmor( $this->msg( 'abusefilter-log-diff' )->parse() ),
					[],
					[
						'diff' => 'prev',
						'target' => $title->getPrefixedText(),
						'timestamp' => $row->ar_timestamp,
					]
				);
			}
		} else {
			$pageLink = WikiMap::makeForeignLink( $row->afl_wiki, $row->afl_title );

			if ( $row->afl_rev_id ) {
				$diffUrl = WikiMap::getForeignURL( $row->afl_wiki, $row->afl_title );
				$diffUrl = wfAppendQuery( $diffUrl,
					[ 'diff' => 'prev', 'oldid' => $row->afl_rev_id ] );

				$diffLink = Linker::makeExternalLink( $diffUrl,
					$this->msg( 'abusefilter-log-diff' )->text() );
			}
		}

		if ( !$row->afl_wiki ) {
			// Local user
			$userLink = self::getUserLinks( $row->afl_user, $row->afl_user_text );
		} else {
			$userLink = WikiMap::foreignUserLink( $row->afl_wiki, $row->afl_user_text );
			$userLink .= ' (' . WikiMap::getWikiName( $row->afl_wiki ) . ')';
		}

		$timestamp = htmlspecialchars( $lang->timeanddate( $row->afl_timestamp, true ) );

		$actions_takenRaw = $row->afl_actions;
		if ( !strlen( trim( $actions_takenRaw ) ) ) {
			$actions_taken = $this->msg( 'abusefilter-log-noactions' )->escaped();
		} else {
			$actions = explode( ',', $actions_takenRaw );
			$displayActions = [];

			$context = $this->getContext();
			foreach ( $actions as $action ) {
				$displayActions[] = AbuseFilter::getActionDisplay( $action, $context );
			}
			$actions_taken = $lang->commaList( $displayActions );
		}

		list( $filterID, $global ) = AbuseFilter::splitGlobalName( $row->afl_filter );

		if ( $global ) {
			// Pull global filter description
			$lookup = AbuseFilterServices::getFilterLookup();
			try {
				$globalDesc = $lookup->getFilter( $filterID, true )->getName();
				$escaped_comments = Sanitizer::escapeHtmlAllowEntities( $globalDesc );
			} catch ( CentralDBNotAvailableException $_ ) {
				$escaped_comments = $this->msg( 'abusefilter-log-description-not-available' )->escaped();
			}
			$filter_hidden = $lookup->getFilter( $filterID, $global )->isHidden();
		} else {
			$escaped_comments = Sanitizer::escapeHtmlAllowEntities(
				$row->af_public_comments );
			$filter_hidden = $row->af_hidden;
		}

		if ( $this->afPermissionManager->canSeeLogDetailsForFilter( $user, $filter_hidden ) ) {
			$actionLinks = [];

			if ( $isListItem ) {
				$detailsLink = $linkRenderer->makeKnownLink(
					$this->getPageTitle( $row->afl_id ),
					$this->msg( 'abusefilter-log-detailslink' )->text()
				);
				$actionLinks[] = $detailsLink;
			}

			$examineTitle = SpecialPage::getTitleFor( 'AbuseFilter', 'examine/log/' . $row->afl_id );
			$examineLink = $linkRenderer->makeKnownLink(
				$examineTitle,
				new HtmlArmor( $this->msg( 'abusefilter-changeslist-examine' )->parse() )
			);
			$actionLinks[] = $examineLink;

			if ( $diffLink ) {
				$actionLinks[] = $diffLink;
			}

			if ( $this->afPermissionManager->canHideAbuseLog( $user ) ) {
				$hideLink = $linkRenderer->makeKnownLink(
					$this->getPageTitle(),
					$this->msg( 'abusefilter-log-hidelink' )->text(),
					[],
					[ 'hide' => $row->afl_id ]
				);

				$actionLinks[] = $hideLink;
			}

			if ( $global ) {
				$centralDb = $this->getConfig()->get( 'AbuseFilterCentralDB' );
				$linkMsg = $this->msg( 'abusefilter-log-detailedentry-global' )
					->numParams( $filterID );
				if ( $centralDb !== null ) {
					$globalURL = WikiMap::getForeignURL(
						$centralDb,
						'Special:AbuseFilter/' . $filterID
					);
					$filterLink = Linker::makeExternalLink( $globalURL, $linkMsg->text() );
				} else {
					$filterLink = $linkMsg->escaped();
				}
			} else {
				$title = SpecialPage::getTitleFor( 'AbuseFilter', $filterID );
				$linkText = $this->msg( 'abusefilter-log-detailedentry-local' )
					->numParams( $filterID )->text();
				$filterLink = $linkRenderer->makeKnownLink( $title, $linkText );
			}
			$description = $this->msg( 'abusefilter-log-detailedentry-meta' )->rawParams(
				$timestamp,
				$userLink,
				$filterLink,
				htmlspecialchars( $row->afl_action ),
				$pageLink,
				$actions_taken,
				$escaped_comments,
				$lang->pipeList( $actionLinks )
			)->params( $row->afl_user_text )->parse();
		} else {
			if ( $diffLink ) {
				$msg = 'abusefilter-log-entry-withdiff';
			} else {
				$msg = 'abusefilter-log-entry';
			}
			$description = $this->msg( $msg )->rawParams(
				$timestamp,
				$userLink,
				htmlspecialchars( $row->afl_action ),
				$pageLink,
				$actions_taken,
				$escaped_comments,
				// Passing $7 to 'abusefilter-log-entry' will do nothing, as it's not used.
				$diffLink
			)->params( $row->afl_user_text )->parse();
		}

		$attribs = null;
		if ( $isHidden === true ) {
			$attribs = [ 'class' => 'mw-abusefilter-log-hidden-entry' ];
		} elseif ( $isHidden === 'implicit' ) {
			$description .= ' ' .
				$this->msg( 'abusefilter-log-hidden-implicit' )->parse();
		}

		if ( $isListItem ) {
			return Xml::tags( 'li', $attribs, $description );
		} else {
			return Xml::tags( 'span', $attribs, $description );
		}
	}

	/**
	 * @param int $userId
	 * @param string $userName
	 * @return string
	 */
	protected static function getUserLinks( $userId, $userName ) {
		static $cache = [];

		if ( !isset( $cache[$userName][$userId] ) ) {
			$cache[$userName][$userId] = Linker::userLink( $userId, $userName ) .
				Linker::userToolLinks( $userId, $userName, true );
		}

		return $cache[$userName][$userId];
	}

	/**
	 * Given a log entry row, decides whether or not it can be viewed by the public.
	 *
	 * @param stdClass $row The abuse_filter_log row object.
	 *
	 * @return bool|string true if the item is explicitly hidden, false if it is not.
	 *    The string 'implicit' if it is hidden because the corresponding revision is hidden.
	 */
	public static function isHidden( $row ) {
		// First, check if the entry is hidden. Since this is an oversight-level deletion,
		// it's more important than the associated revision being deleted.
		if ( $row->afl_deleted ) {
			return true;
		}
		if ( $row->afl_rev_id ) {
			$revision = MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionById( $row->afl_rev_id );
			if ( $revision && $revision->getVisibility() !== 0 ) {
				return 'implicit';
			}
		}

		return false;
	}
}
