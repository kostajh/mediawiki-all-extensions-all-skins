<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;

/**
 * Class containing hooked functions for a FlaggedRevs environment
 */
class FlaggedRevsUIHooks {
	/**
	 * Add FlaggedRevs css/js.
	 *
	 * @param OutputPage $out
	 */
	private static function injectStyleAndJS( OutputPage $out ) {
		static $loadedModules = false;
		if ( $loadedModules ) {
			return; // don't double-load
		}
		$loadedModules = true;
		$fa = FlaggablePageView::globalArticleInstance();
		# Try to only add to relevant pages
		if ( !$fa || !$fa->isReviewable() ) {
			return;
		}
		# Add main CSS & JS files
		$out->addModuleStyles( 'ext.flaggedRevs.basic' );
		$out->addModules( 'ext.flaggedRevs.advanced' );
		# Add review form JS for reviewers
		if ( MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $out->getUser(), 'review' )
		) {
			$out->addModules( 'ext.flaggedRevs.review' );
			$out->addModuleStyles( 'ext.flaggedRevs.review.styles' );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/MakeGlobalVariablesScript
	 *
	 * @param array &$vars
	 * @param OutputPage $out
	 */
	public static function onMakeGlobalVariablesScript( array &$vars, OutputPage $out ) {
		// Get the review tags on this wiki
		$rTags = FlaggedRevs::getJSTagParams();
		if ( $rTags !== null ) {
			// Only register this variable in <head> when needed (T219342).
			$vars['wgFlaggedRevsParams'] = $rTags;
		}

		// Get page-specific meta-data
		$fa = FlaggableWikiPage::getTitleInstance( $out->getTitle() );

		// Try to only add to relevant pages
		if ( $fa && $fa->isReviewable() ) {
			$frev = $fa->getStableRev();
			$stableId = $frev ? $frev->getRevId() : 0;
			$vars['wgStableRevisionId'] = $stableId;
		}
	}

	/**
	 * Add FlaggedRevs css for relevant special pages.
	 * @param OutputPage $out
	 */
	private static function injectStyleForSpecial( $out ) {
		$title = $out->getTitle();
		$spPages = [ 'UnreviewedPages', 'PendingChanges', 'ProblemChanges',
			'Watchlist', 'Recentchanges', 'Contributions', 'Recentchangeslinked' ];
		foreach ( $spPages as $key ) {
			if ( $title->isSpecial( $key ) ) {
				$out->addModuleStyles( 'ext.flaggedRevs.basic' ); // CSS only
				break;
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * Add tag notice, CSS/JS, protect form link, and set robots policy.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( $out, $skin ) {
		if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
			return;
		}

		if ( $out->getTitle()->getNamespace() !== NS_SPECIAL ) {
			$view = FlaggablePageView::singleton();
			$view->addStabilizationLink(); // link on protect form
			$view->displayTag(); // show notice bar/icon in subtitle
			if ( $out->isArticleRelated() ) {
				// Only use this hook if we want to prepend the form.
				// We prepend the form for diffs, so only handle that case here.
				if ( $view->diffRevRecordsAreSet() ) {
					$view->addReviewForm( $out ); // form to be prepended
				}
			}
			$view->setRobotPolicy(); // set indexing policy
			self::injectStyleAndJS( $out ); // full CSS/JS
		} else {
			self::maybeAddBacklogNotice( $out ); // RC/Watchlist notice
			self::injectStyleForSpecial( $out ); // try special page CSS
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 *
	 * Add user preferences (uses prefs-flaggedrevs, prefs-flaggedrevs-ui msgs)
	 * @param User $user
	 * @param array[] &$preferences
	 */
	public static function onGetPreferences( $user, array &$preferences ) {
		// Box or bar UI
		$preferences['flaggedrevssimpleui'] =
			[
				'type' => 'radio',
				'section' => 'rc/flaggedrevs-ui',
				'label-message' => 'flaggedrevs-pref-UI',
				'options' => [
					wfMessage( 'flaggedrevs-pref-UI-0' )->escaped() => 0,
					wfMessage( 'flaggedrevs-pref-UI-1' )->escaped() => 1,
				],
			];
		// Default versions...
		$preferences['flaggedrevsstable'] =
			[
				'type' => 'radio',
				'section' => 'rc/flaggedrevs-ui',
				'label-message' => 'flaggedrevs-prefs-stable',
				'options' => [
					wfMessage( 'flaggedrevs-pref-stable-0' )->escaped() => FR_SHOW_STABLE_DEFAULT,
					wfMessage( 'flaggedrevs-pref-stable-1' )->escaped() => FR_SHOW_STABLE_ALWAYS,
					wfMessage( 'flaggedrevs-pref-stable-2' )->escaped() => FR_SHOW_STABLE_NEVER,
				],
			];
		// Review-related rights...
		if ( MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $user, 'review' )
		) {
			// Watching reviewed pages
			$preferences['flaggedrevswatch'] =
				[
					'type' => 'toggle',
					'section' => 'watchlist/advancedwatchlist',
					'label-message' => 'flaggedrevs-prefs-watch',
				];
			// Diff-to-stable on edit
			$preferences['flaggedrevseditdiffs'] =
				[
					'type' => 'toggle',
					'section' => 'editing/advancedediting',
					'label-message' => 'flaggedrevs-prefs-editdiffs',
				];
			// Diff-to-stable on draft view
			$preferences['flaggedrevsviewdiffs'] =
				[
					'type' => 'toggle',
					'section' => 'rc/flaggedrevs-ui',
					'label-message' => 'flaggedrevs-prefs-viewdiffs',
				];
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ImagePageFindFile
	 *
	 * @param ImagePage $imagePage
	 * @param File|false &$normalFile
	 * @param File|false &$displayFile
	 */
	public static function onImagePageFindFile( $imagePage, &$normalFile, &$displayFile ) {
		if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
			return;
		}

		$view = FlaggablePageView::singleton();
		$view->imagePageFindFile( $normalFile, $displayFile );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinTemplateNavigation
	 *
	 * Vector et al: $links is all the tabs (2 levels)
	 * @param Skin $skin
	 * @param array[] &$links
	 */
	public static function onSkinTemplateNavigationUniversal( Skin $skin, array &$links ) {
		if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
			return;
		}

		if ( FlaggablePageView::globalArticleInstance() != null ) {
			$view = FlaggablePageView::singleton();
			$view->setActionTabs( $skin, $links['actions'] );
			$view->setViewTabs( $skin, $links['views'] );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleViewHeader
	 *
	 * @param Article $article
	 * @param bool &$outputDone
	 * @param bool &$useParserCache
	 */
	public static function onArticleViewHeader( $article, &$outputDone, &$useParserCache ) {
		if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
			return;
		}

		$view = FlaggablePageView::singleton();
		$view->addStableLink();
		$view->setPageContent( $outputDone, $useParserCache );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/InitializeArticleMaybeRedirect
	 *
	 * @param Title $title
	 * @param WebRequest $request
	 * @param bool &$ignoreRedirect
	 * @param string &$target
	 * @param Article $article
	 */
	public static function overrideRedirect(
		Title $title,
		WebRequest $request,
		&$ignoreRedirect,
		&$target,
		Article $article
	) {
		global $wgParserCacheExpireTime;
		$wikiPage = $article->getPage();

		if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
			return;
		}

		$fa = FlaggableWikiPage::getTitleInstance( $title );
		if ( !$fa->isReviewable() ) {
			return;
		}
		# Viewing an old reviewed version...
		if ( $request->getVal( 'stableid' ) ) {
			$ignoreRedirect = true; // don't redirect (same as ?oldid=x)
			return;
		}
		$srev = $fa->getStableRev();
		$view = FlaggablePageView::singleton();
		# Check if we are viewing an unsynced stable version...
		if ( $srev && $view->showingStable() && $srev->getRevId() != $wikiPage->getLatest() ) {
			# Check the stable redirect properties from the cache...
			$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
			$stableRedirect = $cache->getWithSetCallback(
				$cache->makeKey( 'flaggedrevs-stable-redirect', $wikiPage->getId() ),
				$wgParserCacheExpireTime,
				function () use ( $fa, $srev ) {
					$content = $srev->getRevisionRecord()
						->getContent( SlotRecord::MAIN );

					return $fa->getRedirectURL( $content->getUltimateRedirectTarget() ) ?: '';
				},
				[
					'touchedCallback' => function () use ( $wikiPage ) {
						return wfTimestampOrNull( TS_UNIX, $wikiPage->getTouched() );
					}
				]
			);
			if ( $stableRedirect ) {
				$target = $stableRedirect; // use stable redirect
			} else {
				$ignoreRedirect = true; // make MW skip redirection
			}
			$clearEnvironment = (bool)$target;
		# Check if the we are viewing a draft or synced stable version...
		} else {
			# In both cases, we can just let MW use followRedirect()
			# on the draft as normal, avoiding any page text hits.
			$clearEnvironment = $wikiPage->isRedirect();
		}
		# Environment will change in MediaWiki::initializeArticle
		if ( $clearEnvironment ) {
			$view->clear();
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPage::showEditForm:initial
	 *
	 * @param EditPage $editPage
	 */
	public static function addToEditView( $editPage ) {
		$view = FlaggablePageView::singleton();
		$view->addToEditView( $editPage );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/TitleGetEditNotices
	 *
	 * @param Title $title
	 * @param int $oldid
	 * @param string[] &$notices
	 */
	public static function getEditNotices( $title, $oldid, &$notices ) {
		$view = FlaggablePageView::singleton();
		$view->getEditNotices( $title, $oldid, $notices );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageBeforeEditButtons
	 *
	 * @param EditPage $editPage
	 * @param \OOUI\ButtonInputWidget[] &$buttons
	 */
	public static function onBeforeEditButtons( $editPage, &$buttons ) {
		$view = FlaggablePageView::singleton();
		$view->changeSaveButton( $editPage, $buttons );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageNoSuchSection
	 *
	 * @param EditPage $editPage
	 * @param string &$s
	 */
	public static function onNoSuchSection( $editPage, &$s ) {
		$view = FlaggablePageView::singleton();
		$view->addToNoSuchSection( $editPage, $s );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageHistoryBeforeList
	 *
	 * @param Article $article
	 */
	public static function addToHistView( $article ) {
		$view = FlaggablePageView::singleton();
		$view->addToHistView();
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/CategoryPageView
	 *
	 * @param CategoryPage $category
	 */
	public static function onCategoryPageView( $category ) {
		if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
			return;
		}

		$view = FlaggablePageView::singleton();
		$view->addToCategoryView();
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinAfterContent
	 *
	 * @param string &$data
	 */
	public static function onSkinAfterContent( &$data ) {
		if ( defined( 'MW_HTML_FOR_DUMP' ) ) {
			return;
		}

		global $wgOut;
		if ( $wgOut->isArticleRelated()
			&& FlaggablePageView::globalArticleInstance() != null
		) {
			$view = FlaggablePageView::singleton();
			// Only use this hook if we want to append the form.
			// We *prepend* the form for diffs, so skip that case here.
			if ( !$view->diffRevRecordsAreSet() ) {
				$view->addReviewForm( $data ); // form to be appended
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialNewPagesFilters
	 *
	 * Registers a filter on Special:NewPages to hide edits that have been reviewed
	 * through FlaggedRevs.
	 *
	 * @param SpecialPage $specialPage
	 * @param array[] &$filters
	 */
	public static function addHideReviewedUnstructuredFilter( $specialPage, &$filters ) {
		if ( !FlaggedRevs::useSimpleConfig() ) {
			$filters['hideReviewed'] = [
				'msg' => 'flaggedrevs-hidereviewed', 'default' => false
			];
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangesListSpecialPageStructuredFilters
	 *
	 * Registers a filter to hide edits that have been reviewed through
	 * FlaggedRevs.
	 *
	 * @param ChangesListSpecialPage $specialPage Special page, such as
	 *   Special:RecentChanges or Special:Watchlist
	 */
	public static function addHideReviewedFilter( ChangesListSpecialPage $specialPage ) {
		if ( FlaggedRevs::useSimpleConfig() ) {
			return;
		}

		// Old filter, replaced in structured UI
		$flaggedRevsUnstructuredGroup = new ChangesListBooleanFilterGroup(
			[
				'name' => 'flaggedRevsUnstructured',
				'priority' => -1,
				'filters' => [
					[
						'name' => 'hideReviewed',
						'showHide' => 'flaggedrevs-hidereviewed',
						'isReplacedInStructuredUi' => true,
						'default' => false,
						'queryCallable' => function ( $specialClassName, $ctx, $dbr, &$tables,
							&$fields, &$conds, &$query_options, &$join_conds
						) {
							self::hideReviewedChangesUnconditionally(
								$conds
							);
						},
					],
				],
			]
		);

		$specialPage->registerFilterGroup( $flaggedRevsUnstructuredGroup );

		$flaggedRevsGroup = new ChangesListStringOptionsFilterGroup(
			[
				'name' => 'flaggedrevs',
				'title' => 'flaggedrevs',
				'priority' => -9,
				'default' => ChangesListStringOptionsFilterGroup::NONE,
				'isFullCoverage' => true,
				'filters' => [
					[
						'name' => 'needreview',
						'label' => 'flaggedrevs-rcfilters-need-review-label',
						'description' => 'flaggedrevs-rcfilters-need-review-desc',
						'cssClassSuffix' => 'need-review',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = FlaggedRevs::getReviewNamespaces();
							return ( in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								$rc->getAttribute( 'rc_type' ) !== RC_EXTERNAL ) &&
								(
									!$rc->getAttribute( 'fp_stable' ) ||
									(
										// The rc_timestamp >= fp_pending_since condition implies that
										// fp_pending_since is not null, because all comparisons with null
										// values are false in MySQL. It doesn't work that way in PHP,
										// so we have to explicitly check that fp_pending_since is not null
										$rc->getAttribute( 'fp_pending_since' ) &&
										$rc->getAttribute( 'rc_timestamp' ) >= $rc->getAttribute( 'fp_pending_since' )
									)
								);
						}
					],
					[
						'name' => 'reviewed',
						'label' => 'flaggedrevs-rcfilters-reviewed-label',
						'description' => 'flaggedrevs-rcfilters-reviewed-desc',
						'cssClassSuffix' => 'reviewed',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = FlaggedRevs::getReviewNamespaces();
							return ( in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces ) &&
								$rc->getAttribute( 'rc_type' ) !== RC_EXTERNAL ) &&
								$rc->getAttribute( 'fp_stable' ) &&
								(
									!$rc->getAttribute( 'fp_pending_since' ) ||
									$rc->getAttribute( 'rc_timestamp' ) < $rc->getAttribute( 'fp_pending_since' )
								);
						}
					],
					[
						'name' => 'notreviewable',
						'label' => 'flaggedrevs-rcfilters-not-reviewable-label',
						'description' => 'flaggedrevs-rcfilters-not-reviewable-desc',
						'cssClassSuffix' => 'not-reviewable',
						'isRowApplicableCallable' => function ( $ctx, $rc ) {
							$namespaces = FlaggedRevs::getReviewNamespaces();
							return !in_array( $rc->getAttribute( 'rc_namespace' ), $namespaces );
						}
					],
				],
				'queryCallable' => function ( $specialClassName, $ctx, $dbr, &$tables,
					&$fields, &$conds, &$query_options, &$join_conds, $selectedValues
				) {
					$fields[] = 'fp_stable';
					$fields[] = 'fp_pending_since';
					$fields[] = 'rc_namespace';

					$namespaces = FlaggedRevs::getReviewNamespaces();
					$needReviewCond = 'rc_timestamp >= fp_pending_since OR fp_stable IS NULL';
					$reviewedCond = '(fp_pending_since IS NULL OR rc_timestamp < fp_pending_since) ' .
						'AND fp_stable IS NOT NULL';
					$notReviewableCond = 'rc_namespace NOT IN (' . $dbr->makeList( $namespaces ) .
						') OR rc_type = ' . $dbr->addQuotes( RC_EXTERNAL );
					$reviewableCond = 'rc_namespace IN (' . $dbr->makeList( $namespaces ) .
						') AND rc_type != ' . $dbr->addQuotes( RC_EXTERNAL );

					if ( $selectedValues === [ 'needreview', 'notreviewable', 'reviewed' ] ) {
						// no filters
						return;
					}

					if ( $selectedValues === [ 'needreview', 'reviewed' ] ) {
						$conds[] = $reviewableCond;
						return;
					}

					if ( $selectedValues === [ 'needreview', 'notreviewable' ] ) {
						$conds[] = $dbr->makeList( [
							$notReviewableCond,
							$needReviewCond
						], LIST_OR );
						return;
					}

					if ( $selectedValues === [ 'notreviewable', 'reviewed' ] ) {
						$conds[] = $dbr->makeList( [
							$notReviewableCond,
							$reviewedCond
						], LIST_OR );
						return;
					}

					if ( $selectedValues === [ 'needreview' ] ) {
						$conds[] = $dbr->makeList( [
							$reviewableCond,
							$needReviewCond
						], LIST_AND );
						return;
					}

					if ( $selectedValues === [ 'notreviewable' ] ) {
						$conds[] = $notReviewableCond;
						return;
					}

					if ( $selectedValues === [ 'reviewed' ] ) {
						$conds[] = $dbr->makeList( [
							$reviewableCond,
							$reviewedCond
						], LIST_AND );
						return;
					}
				}
			]
		);

		$specialPage->registerFilterGroup( $flaggedRevsGroup );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageHistoryPager::getQueryInfo
	 *
	 * @param HistoryPager $pager
	 * @param array &$queryInfo
	 */
	public static function addToHistQuery( HistoryPager $pager, array &$queryInfo ) {
		$flaggedArticle = FlaggableWikiPage::getTitleInstance( $pager->getTitle() );
		# Non-content pages cannot be validated. Stable version must exist.
		if ( $flaggedArticle->isReviewable() && $flaggedArticle->getStableRev() ) {
			# Highlight flaggedrevs
			$queryInfo['tables'][] = 'flaggedrevs';
			$queryInfo['fields'][] = 'fr_quality';
			$queryInfo['fields'][] = 'fr_user';
			$queryInfo['fields'][] = 'fr_flags';
			$queryInfo['join_conds']['flaggedrevs'] = [ 'LEFT JOIN', "fr_rev_id = rev_id" ];
			# Find reviewer name. Sanity check that no extensions added a `user` query.
			// @phan-suppress-next-line PhanSuspiciousWeakTypeComparison
			if ( !in_array( 'user', $queryInfo['tables'] ) ) {
				$queryInfo['tables'][] = 'user';
				$queryInfo['fields'][] = 'user_name AS reviewer';
				$queryInfo['join_conds']['user'] = [ 'LEFT JOIN', "user_id = fr_user" ];
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LocalFile::getHistory
	 *
	 * @param File $file
	 * @param array &$tables
	 * @param array &$fields
	 * @param array &$conds
	 * @param array &$opts
	 * @param array &$join_conds
	 */
	public static function addToFileHistQuery(
		File $file, array &$tables, array &$fields, &$conds, array &$opts, array &$join_conds
	) {
		if (
			defined( 'MW_HTML_FOR_DUMP' )
			|| !$file->isLocal() // local files only
		) {
			return;
		}
		$flaggedArticle = FlaggableWikiPage::getTitleInstance( $file->getTitle() );
		# Non-content pages cannot be validated. Stable version must exist.
		if ( $flaggedArticle->isReviewable() && $flaggedArticle->getStableRev() ) {
			$tables[] = 'flaggedrevs';
			$fields[] = 'MAX(fr_quality) AS fr_quality';
			# Avoid duplicate rows due to multiple revs with the same sha-1 key

			# This is a stupid hack to get all the field names in our GROUP BY
			# clause. Postgres yells at you for not including all of the selected
			# columns, so grab the full list, unset the two we actually want to
			# order by, then append the rest of them to our two. It would be
			# REALLY nice if we handled this automagically in makeSelectOptions()
			# or something *sigh*
			$groupBy = OldLocalFile::getQueryInfo()['fields'];
			unset( $groupBy[ array_search( 'oi_name', $groupBy ) ] );
			unset( $groupBy[ array_search( 'oi_timestamp', $groupBy ) ] );
			array_unshift( $groupBy, 'oi_name', 'oi_timestamp' );
			$opts['GROUP BY'] = implode( ',', $groupBy );

			$join_conds['flaggedrevs'] = [ 'LEFT JOIN',
				'oi_sha1 = fr_img_sha1 AND oi_timestamp = fr_img_timestamp' ];
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ContribsPager::getQueryInfo
	 *
	 * @param ContribsPager $pager
	 * @param array &$queryInfo
	 */
	public static function addToContribsQuery( $pager, array &$queryInfo ) {
		global $wgFlaggedRevsProtection;

		if ( $wgFlaggedRevsProtection ) {
			return;
		}

		# Highlight flaggedrevs
		$queryInfo['tables'][] = 'flaggedrevs';
		$queryInfo['fields'][] = 'fr_quality';
		$queryInfo['join_conds']['flaggedrevs'] = [ 'LEFT JOIN', "fr_rev_id = rev_id" ];
		# Highlight unchecked content
		$queryInfo['tables'][] = 'flaggedpages';
		$queryInfo['fields'][] = 'fp_stable';
		$queryInfo['fields'][] = 'fp_pending_since';
		$queryInfo['join_conds']['flaggedpages'] = [ 'LEFT JOIN', "fp_page_id = rev_page" ];
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialNewpagesConditions
	 *
	 * @param NewPagesPager $specialPage
	 * @param FormOptions $opts
	 * @param array &$conds
	 * @param array &$tables
	 * @param string[] &$fields
	 * @param array &$join_conds
	 */
	public static function modifyNewPagesQuery(
		$specialPage, $opts, &$conds, &$tables, &$fields, &$join_conds
	) {
		self::makeAllQueryChanges( $conds, $tables, $join_conds, $fields );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangesListSpecialPageQuery
	 *
	 * @param string $name
	 * @param array &$tables
	 * @param array &$fields
	 * @param array &$conds
	 * @param array &$query_options
	 * @param array &$join_conds
	 * @param FormOptions $opts
	 */
	public static function modifyChangesListSpecialPageQuery(
		$name, &$tables, &$fields, &$conds, &$query_options, &$join_conds, $opts
	) {
		self::addMetadataQueryJoins( $tables, $join_conds, $fields );
	}

	/**
	 * Make all query changes, both joining for FlaggedRevs metadata and conditionally
	 * hiding reviewed changes
	 *
	 * @param array &$conds Query conditions
	 * @param array &$tables Tables to query
	 * @param array &$join_conds Query join conditions
	 * @param string[] &$fields Fields to query
	 */
	private static function makeAllQueryChanges(
		array &$conds, array &$tables, array &$join_conds, array &$fields
	) {
		self::addMetadataQueryJoins( $tables, $join_conds, $fields );
		self::hideReviewedChangesIfNeeded( $conds );
	}

	/**
	 * Add FlaggedRevs metadata by adding fields and joins
	 *
	 * @param array &$tables Tables to query
	 * @param array &$join_conds Query join conditions
	 * @param string[] &$fields Fields to query
	 */
	private static function addMetadataQueryJoins(
		array &$tables, array &$join_conds, array &$fields
	) {
		$tables[] = 'flaggedpages';
		$fields[] = 'fp_stable';
		$fields[] = 'fp_pending_since';
		$join_conds['flaggedpages'] = [ 'LEFT JOIN', 'fp_page_id = rc_cur_id' ];
	}

	/**
	 * Checks the request variable and hides reviewed changes if requested
	 *
	 * Must already be joined into the FlaggedRevs tables.
	 *
	 * @param array &$conds Query conditions
	 */
	private static function hideReviewedChangesIfNeeded(
		array &$conds
	) {
		global $wgRequest;

		if ( $wgRequest->getBool( 'hideReviewed' ) && !FlaggedRevs::useSimpleConfig() ) {
			self::hideReviewedChangesUnconditionally( $conds );
		}
	}

	/**
	 * Hides reviewed changes unconditionally; assumes you have checked whether to do
	 * so already
	 *
	 * Must already be joined into the FlaggedRevs tables.
	 *
	 * @param array &$conds Query conditions
	 */
	private static function hideReviewedChangesUnconditionally(
		array &$conds
	) {
		// Don't filter external changes as FlaggedRevisions doesn't apply to those
		$conds[] = 'rc_timestamp >= fp_pending_since OR fp_stable IS NULL OR rc_type = ' . RC_EXTERNAL;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageHistoryLineEnding
	 *
	 * @param HistoryPager $history
	 * @param stdClass $row
	 * @param string &$s
	 * @param string[] &$liClasses
	 * @suppress PhanUndeclaredProperty For HistoryPager->fr_*
	 */
	public static function addToHistLine( HistoryPager $history, $row, &$s, &$liClasses ) {
		$fa = FlaggableWikiPage::getTitleInstance( $history->getTitle() );
		if ( !$fa->isReviewable() ) {
			return;
		}
		# Fetch and process cache the stable revision
		if ( !isset( $history->fr_stableRevId ) ) {
			$srev = $fa->getStableRev();
			$history->fr_stableRevId = $srev ? $srev->getRevId() : null;
			$history->fr_stableRevUTS = $srev ? // bug 15515
				wfTimestamp( TS_UNIX, $srev->getRevTimestamp() ) : null;
			$history->fr_pendingRevs = false;
		}
		if ( !$history->fr_stableRevId ) {
			return;
		}
		$title = $history->getTitle();
		$revId = (int)$row->rev_id;
		// Pending revision: highlight and add diff link
		$link = '';
		$class = '';
		if ( wfTimestamp( TS_UNIX, $row->rev_timestamp ) > $history->fr_stableRevUTS ) {
			$class = 'flaggedrevs-pending';
			$link = $history->msg( 'revreview-hist-pending-difflink',
				$title->getPrefixedText(), $history->fr_stableRevId, $revId )->parse();
			$link = '<span class="plainlinks mw-fr-hist-difflink">' . $link . '</span>';
			$history->fr_pendingRevs = true; // pending rev shown above stable
		// Reviewed revision: highlight and add link
		} elseif ( isset( $row->fr_quality ) ) {
			if ( !( $row->rev_deleted & RevisionRecord::DELETED_TEXT ) ) {
				# Add link to stable version of *this* rev, if any
				list( $link, $class ) = self::markHistoryRow( $history, $title, $row );
				# Space out and demark the stable revision
				if ( $revId == $history->fr_stableRevId && $history->fr_pendingRevs ) {
					$liClasses[] = 'fr-hist-stable-margin';
				}
			}
		}
		# Style the row as needed
		if ( $class ) {
			$s = "<span class='$class'>$s</span>";
		}
		# Add stable old version link
		if ( $link ) {
			$s .= " $link";
		}
	}

	/**
	 * Make stable version link and return the css
	 * @param IContextSource $ctx
	 * @param Title $title
	 * @param stdClass $row from history page
	 * @return string[]
	 */
	private static function markHistoryRow( IContextSource $ctx, Title $title, $row ) {
		if ( !isset( $row->fr_quality ) ) {
			return [ "", "" ]; // not reviewed
		}
		$liCss = FlaggedRevsXML::getQualityColor( $row->fr_quality );
		$flags = explode( ',', $row->fr_flags );
		if ( in_array( 'auto', $flags ) ) {
			$msg = ( $row->fr_quality >= 1 )
				? 'revreview-hist-quality-auto'
				: 'revreview-hist-basic-auto';
			$css = ( $row->fr_quality >= 1 )
				? 'fr-hist-quality-auto'
				: 'fr-hist-basic-auto';
		} else {
			$msg = ( $row->fr_quality >= 1 )
				? 'revreview-hist-quality-user'
				: 'revreview-hist-basic-user';
			$css = ( $row->fr_quality >= 1 )
				? 'fr-hist-quality-user'
				: 'fr-hist-basic-user';
		}
		$name = $row->reviewer ?? User::whoIs( $row->fr_user );
		$link = $ctx->msg( $msg, $title->getPrefixedDBkey(), $row->rev_id, $name )->parse();
		$link = "<span class='$css plainlinks'>[$link]</span>";
		return [ $link, $liCss ];
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ImagePageFileHistoryLine
	 *
	 * @param ImageHistoryList $hist
	 * @param File $file
	 * @param string &$s
	 * @param string|null &$rowClass
	 */
	public static function addToFileHistLine( $hist, File $file, &$s, &$rowClass ) {
		if (
			defined( 'MW_HTML_FOR_DUMP' )
			|| !$file->isVisible() // Don't bother showing notice for deleted revs
		) {
			return;
		}
		# Quality level for old versions selected all at once.
		# Commons queries cannot be done all at once...
		if ( !$file->isOld() || !$file->isLocal() ) {
			$dbr = wfGetDB( DB_REPLICA );
			$quality = $dbr->selectField( 'flaggedrevs', 'fr_quality',
				[ 'fr_img_sha1' => $file->getSha1(),
					'fr_img_timestamp' => $dbr->timestamp( $file->getTimestamp() ) ],
				__METHOD__
			);
		} else {
			$quality = $file->quality === null ? false : $file->quality;
		}
		# If reviewed, class the line
		if ( $quality !== false ) {
			$rowClass = FlaggedRevsXML::getQualityColor( $quality );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ContributionsLineEnding
	 *
	 * Intercept contribution entries and format them to FlaggedRevs standards
	 *
	 * @param SpecialPage $contribs SpecialPage object for contributions
	 * @param string &$ret the HTML line
	 * @param stdClass $row Row the DB row for this line
	 * @param string[] &$classes the classes to add to the surrounding <li>
	 */
	public static function addToContribsLine( $contribs, &$ret, $row, &$classes ) {
		global $wgFlaggedRevsProtection;

		// make sure that we're parsing revisions data
		if ( !$wgFlaggedRevsProtection && isset( $row->rev_id ) ) {
			$namespaces = FlaggedRevs::getReviewNamespaces();
			if ( !in_array( $row->page_namespace, $namespaces ) ) {
				// do nothing
			} elseif ( isset( $row->fr_quality ) ) {
				$classes[] = FlaggedRevsXML::getQualityColor( $row->fr_quality );
			} elseif ( isset( $row->fp_pending_since )
				&& $row->rev_timestamp >= $row->fp_pending_since // bug 15515
			) {
				$classes[] = 'flaggedrevs-pending';
			} elseif ( !isset( $row->fp_stable ) ) {
				$classes[] = 'flaggedrevs-unreviewed';
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ChangesListInsertArticleLink
	 *
	 * @param ChangesList $list
	 * @param string &$articlelink
	 * @param string &$s
	 * @param RecentChange $rc
	 * @param bool $unpatrolled
	 * @param bool $watched
	 */
	public static function addToChangeListLine(
		$list,
		&$articlelink,
		&$s,
		RecentChange $rc,
		$unpatrolled,
		$watched
	) {
		$title = $rc->getTitle(); // convenience
		if ( !FlaggedRevs::inReviewNamespace( $title )
			|| empty( $rc->getAttribute( 'rc_this_oldid' ) ) // rev, not log
			|| !array_key_exists( 'fp_stable', $rc->getAttributes() )
		) {
			// Confirm that page is in reviewable namespace
			return;
		}
		$rlink = '';
		$css = '';
		// page is not reviewed
		if ( $rc->getAttribute( 'fp_stable' ) == null ) {
			// Is this a config were pages start off reviewable?
			// Hide notice from non-reviewers due to vandalism concerns (bug 24002).
			if ( !FlaggedRevs::useSimpleConfig() && MediaWikiServices::getInstance()
					->getPermissionManager()
					->userHasRight( $list->getUser(), 'review' )
			) {
				$rlink = wfMessage( 'revreview-unreviewedpage' )->escaped();
				$css = 'flaggedrevs-unreviewed';
			}
		// page is reviewed and has pending edits (use timestamps; bug 15515)
		} elseif ( $rc->getAttribute( 'fp_pending_since' ) !== null &&
			$rc->getAttribute( 'rc_timestamp' ) >= $rc->getAttribute( 'fp_pending_since' )
		) {
			$rlink = Linker::link(
				$title,
				wfMessage( 'revreview-reviewlink' )->escaped(),
				[ 'title' => wfMessage( 'revreview-reviewlink-title' )->text() ],
				[ 'oldid' => $rc->getAttribute( 'fp_stable' ), 'diff' => 'cur' ] +
					FlaggedRevs::diffOnlyCGI()
			);
			$css = 'flaggedrevs-pending';
		}
		if ( $rlink != '' ) {
			$articlelink .= " <span class=\"mw-fr-reviewlink $css\">[$rlink]</span>";
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleUpdateBeforeRedirect
	 *
	 * @param Article $article
	 * @param string &$sectionAnchor
	 * @param string &$extraQuery
	 */
	public static function injectPostEditURLParams( $article, &$sectionAnchor, &$extraQuery ) {
		if ( FlaggablePageView::globalArticleInstance() != null ) {
			$view = FlaggablePageView::singleton();
			$view->injectPostEditURLParams( $sectionAnchor, $extraQuery );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/NewDifferenceEngine
	 *
	 * diff=review param (bug 16923)
	 * @param Title $titleObj
	 * @param int &$mOldid
	 * @param int &$mNewid
	 * @param string $old
	 * @param string $new
	 */
	public static function checkDiffUrl( $titleObj, &$mOldid, &$mNewid, $old, $new ) {
		if ( $new === 'review' && isset( $titleObj ) ) {
			$sRevId = FlaggedRevision::getStableRevId( $titleObj );
			if ( $sRevId ) {
				$mOldid = $sRevId; // stable
				$mNewid = 0; // cur
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/DifferenceEngineViewHeader
	 *
	 * @param DifferenceEngine $diff
	 */
	public static function onDifferenceEngineViewHeader( DifferenceEngine $diff ) {
		self::injectStyleAndJS( $diff->getOutput() );
		$view = FlaggablePageView::singleton();

		$oldRevRecord = $diff->getOldRevision();
		$newRevRecord = $diff->getNewRevision();
		$view->setViewFlags( $diff, $oldRevRecord, $newRevRecord );
		$view->addToDiffView( $diff, $oldRevRecord, $newRevRecord );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPage::showEditForm:fields
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $out
	 */
	public static function addRevisionIDField( $editPage, $out ) {
		$view = FlaggablePageView::singleton();
		$view->addRevisionIDField( $editPage, $out );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageGetCheckboxesDefinition
	 *
	 * @param EditPage $editPage
	 * @param array &$checkboxes
	 */
	public static function onEditPageGetCheckboxesDefinition( $editPage, &$checkboxes ) {
		$view = FlaggablePageView::singleton();
		$view->addReviewCheck( $editPage, $checkboxes );
	}

	private static function maybeAddBacklogNotice( OutputPage $out ) {
		if ( !MediaWikiServices::getInstance()->getPermissionManager()
			->userHasRight( $out->getUser(), 'review' ) ) {
			// Not relevant to user
			return;
		}
		$namespaces = FlaggedRevs::getReviewNamespaces();
		$watchlist = SpecialPage::getTitleFor( 'Watchlist' );
		# Add notice to watchlist about pending changes...
		if ( $out->getTitle()->equals( $watchlist ) && $namespaces ) {
			$dbr = wfGetDB( DB_REPLICA, 'watchlist' ); // consistency with watchlist
			$watchedOutdated = (bool)$dbr->selectField(
				[ 'watchlist', 'page', 'flaggedpages' ],
				'1', // existence
				[ 'wl_user' => $out->getUser()->getId(), // this user
					'wl_namespace' => $namespaces, // reviewable
					'wl_namespace = page_namespace',
					'wl_title = page_title',
					'fp_page_id = page_id',
					'fp_pending_since IS NOT NULL', // edits pending
				], __METHOD__
			);
			# Give a notice if pages on the users's wachlist have pending edits
			if ( $watchedOutdated ) {
				$css = 'plainlinks fr-watchlist-pending-notice warningbox';
				// @todo: Use Html::warningBox. We can't use it here because warningBox cannot have an id.
				// Thus we must either remove the need of the id attribute or add support in core.
				$out->prependHTML( "<div id='mw-fr-watchlist-pending-notice' class='$css'>" .
					wfMessage( 'flaggedrevs-watched-pending' )->parse() . "</div>" );
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ProtectionForm::buildForm
	 *
	 * Add selector of review "protection" options
	 * Code stolen from Stabilization (which was stolen from ProtectionForm)
	 * @param Article $article
	 * @param string &$output
	 */
	public static function onProtectionForm(
		Article $article,
		&$output
	) {
		global $wgOut, $wgRequest, $wgLang, $wgFlaggedRevsProtection;
		$wikiPage = $article->getPage();
		$title = $wikiPage->getTitle();

		if (
			!$wgFlaggedRevsProtection
			|| !$wikiPage->exists()
			|| !FlaggedRevs::inReviewNamespace( $title ) // not a reviewable page
		) {
			return;
		}
		$form = new PageStabilityProtectForm( $article->getContext()->getUser() );
		$form->setPage( $title );
		# Can the user actually do anything?
		$isAllowed = $form->isAllowed();
		$disabledAttrib = $isAllowed ?
			[] : [ 'disabled' => 'disabled' ];

		# Get the current config/expiry
		$mode = $wgRequest->wasPosted() ? FR_MASTER : 0;
		$config = FRPageConfig::getStabilitySettings( $title, $mode );
		$oldExpirySelect = ( $config['expiry'] == 'infinity' ) ? 'infinite' : 'existing';

		# Load requested restriction level, default to current level...
		$restriction = $wgRequest->getVal( 'mwStabilityLevel',
			FRPageConfig::getProtectionLevel( $config ) );
		# Load the requested expiry time (dropdown)
		$expirySelect = $wgRequest->getVal( 'mwStabilizeExpirySelection', $oldExpirySelect );
		# Load the requested expiry time (field)
		$expiryOther = $wgRequest->getVal( 'mwStabilizeExpiryOther', '' );
		if ( $expiryOther != '' ) {
			$expirySelect = 'othertime'; // mutual exclusion
		}

		# Add an extra row to the protection fieldset tables.
		# Includes restriction dropdown and expiry dropdown & field.
		$output .= "<tr><td>";
		$output .= Xml::openElement( 'fieldset' );
		$legendMsg = wfMessage( 'flaggedrevs-protect-legend' )->parse();
		$output .= "<legend>{$legendMsg}</legend>";
		# Add a "no restrictions" level
		$effectiveLevels = FlaggedRevs::getRestrictionLevels();
		array_unshift( $effectiveLevels, "none" );
		# Show all restriction levels in a <select>...
		$attribs = [
			'id'    => 'mwStabilityLevel',
			'name'  => 'mwStabilityLevel',
			'size'  => count( $effectiveLevels ),
		] + $disabledAttrib;
		$output .= Xml::openElement( 'select', $attribs );
		foreach ( $effectiveLevels as $limit ) {
			if ( $limit == 'none' ) {
				$label = wfMessage( 'flaggedrevs-protect-none' )->text();
			} else {
				$label = wfMessage( 'flaggedrevs-protect-' . $limit )->text();
			}
			// Default to the key itself if no UI message
			if ( wfMessage( 'flaggedrevs-protect-' . $limit )->isDisabled() ) {
				$label = 'flaggedrevs-protect-' . $limit;
			}
			$output .= Xml::option( $label, $limit, $limit == $restriction );
		}
		$output .= Xml::closeElement( 'select' );

		# Get expiry dropdown <select>...
		$scExpiryOptions = wfMessage( 'protect-expiry-options' )->inContentLanguage()->text();
		$showProtectOptions = ( $scExpiryOptions !== '-' && $isAllowed );
		# Add the current expiry as an option
		$expiryFormOptions = '';
		if ( $config['expiry'] != 'infinity' ) {
			$timestamp = $wgLang->timeanddate( $config['expiry'] );
			$d = $wgLang->date( $config['expiry'] );
			$t = $wgLang->time( $config['expiry'] );
			$expiryFormOptions .=
				Xml::option(
					wfMessage( 'protect-existing-expiry', $timestamp, $d, $t )->text(),
					'existing',
					$expirySelect == 'existing'
				) . "\n";
		}
		$expiryFormOptions .= Xml::option( wfMessage(
				'protect-othertime-op' )->text(),
				'othertime'
		) . "\n";
		# Add custom dropdown levels (from MediaWiki message)
		foreach ( explode( ',', $scExpiryOptions ) as $option ) {
			$pair = explode( ':', $option, 2 );
			$show = $pair[0];
			$value = $pair[1] ?? $show;
			$expiryFormOptions .= Xml::option( $show, $value, $expirySelect == $value ) . "\n";
		}
		# Actually add expiry dropdown to form
		$output .= "<table>"; // expiry table start
		if ( $showProtectOptions && $isAllowed ) {
			$output .= "
				<tr>
					<td class='mw-label'>" .
						Xml::label( wfMessage( 'stabilization-expiry' )->text(),
							'mwStabilizeExpirySelection' ) .
					"</td>
					<td class='mw-input'>" .
						Xml::tags( 'select',
							[
								'id'        => 'mwStabilizeExpirySelection',
								'name'      => 'mwStabilizeExpirySelection',
								'onchange'  => 'onFRChangeExpiryDropdown()',
							] + $disabledAttrib,
							$expiryFormOptions ) .
					"</td>
				</tr>";
		}
		# Add custom expiry field to form
		$attribs = [ 'id' => 'mwStabilizeExpiryOther',
			'onkeyup' => 'onFRChangeExpiryField()' ] + $disabledAttrib;
		$output .= "
			<tr>
				<td class='mw-label'>" .
					Xml::label(
						wfMessage( 'stabilization-othertime' )->text(),
						'mwStabilizeExpiryOther'
					) .
				'</td>
				<td class="mw-input">' .
					Xml::input( 'mwStabilizeExpiryOther', 50, $expiryOther, $attribs ) .
				'</td>
			</tr>';
		$output .= "</table>"; // expiry table end
		# Close field set and table row
		$output .= Xml::closeElement( 'fieldset' );
		$output .= "</td></tr>";

		# Add some javascript for expiry dropdowns
		$wgOut->addScript(
			"<script type=\"text/javascript\">
				function onFRChangeExpiryDropdown() {
					document.getElementById('mwStabilizeExpiryOther').value = '';
				}
				function onFRChangeExpiryField() {
					document.getElementById('mwStabilizeExpirySelection').value = 'othertime';
				}
			</script>"
		);
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ProtectionForm::showLogExtract
	 *
	 * Add stability log extract to protection form
	 * @param Article $article
	 * @param OutputPage $out
	 */
	public static function insertStabilityLog(
		Article $article,
		OutputPage $out
	) {
		global $wgFlaggedRevsProtection;
		$wikiPage = $article->getPage();
		$title = $wikiPage->getTitle();

		if (
			!$wgFlaggedRevsProtection
			|| !$wikiPage->exists()
			|| !FlaggedRevs::inReviewNamespace( $title ) // not a reviewable page
		) {
			return;
		}

		# Show relevant lines from the stability log:
		$logPage = new LogPage( 'stable' );
		$out->addHTML( Xml::element( 'h2', null, $logPage->getName()->text() ) );
		LogEventsList::showLogExtract( $out, 'stable', $title->getPrefixedText() );
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ProtectionForm::save
	 *
	 * Update stability config from request
	 * @param Article $article
	 * @param string &$errorMsg
	 */
	public static function onProtectionSave( Article $article, &$errorMsg ) {
		global $wgRequest, $wgFlaggedRevsProtection;
		$wikiPage = $article->getPage();
		$title = $wikiPage->getTitle();
		$user = $article->getContext()->getUser();

		if (
			!$wgFlaggedRevsProtection
			|| !$wikiPage->exists() // simple custom levels set for action=protect
			|| !FlaggedRevs::inReviewNamespace( $title ) // not a reviewable page
		) {
			return;
		}

		if ( wfReadOnly() || !MediaWikiServices::getInstance()->getPermissionManager()
				->userHasRight( $user, 'stablesettings' )
		) {
			// User cannot change anything
			return;
		}
		$form = new PageStabilityProtectForm( $user );
		$form->setPage( $title ); // target page
		$permission = $wgRequest->getVal( 'mwStabilityLevel' );
		if ( $permission == "none" ) {
			$permission = ''; // 'none' => ''
		}
		$form->setAutoreview( $permission ); // protection level (autoreview restriction)
		$form->setWatchThis( null ); // protection form already has a watch check
		$form->setReasonExtra( $wgRequest->getText( 'mwProtect-reason' ) ); // manual
		$form->setReasonSelection( $wgRequest->getVal( 'wpProtectReasonSelection' ) ); // dropdown
		$form->setExpiryCustom( $wgRequest->getVal( 'mwStabilizeExpiryOther' ) ); // manual
		$form->setExpirySelection( $wgRequest->getVal( 'mwStabilizeExpirySelection' ) ); // dropdown
		$form->ready(); // params all set
		if ( $wgRequest->wasPosted() && $form->isAllowed() ) {
			$status = $form->submit();
			if ( $status !== true ) {
				$errorMsg = wfMessage( $status )->text(); // some error message
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialPage_initList
	 *
	 * @param array &$list
	 */
	public static function onSpecialPageInitList( array &$list ) {
		global $wgFlaggedRevsProtection, $wgFlaggedRevsNamespaces, $wgUseTagFilter;

		// Show special pages only if FlaggedRevs is enabled on some namespaces
		if ( count( $wgFlaggedRevsNamespaces ) ) {
			$list['RevisionReview'] = 'RevisionReview'; // unlisted
			$list['ReviewedVersions'] = 'ReviewedVersions'; // unlisted
			$list['PendingChanges'] = 'PendingChanges';
			// Show tag filtered pending edit page if there are tags
			if ( $wgUseTagFilter ) {
				$list['ProblemChanges'] = 'ProblemChanges';
			}
			if ( !$wgFlaggedRevsProtection ) {
				$list['ReviewedPages'] = 'ReviewedPages';
				$list['UnreviewedPages'] = 'UnreviewedPages';
			}
			$list['QualityOversight'] = 'QualityOversight';
			$list['ValidationStatistics'] = 'ValidationStatistics';
			// Protect levels define allowed stability settings
			if ( $wgFlaggedRevsProtection ) {
				$list['StablePages'] = 'StablePages';
			} else {
				$list['ConfiguredPages'] = 'ConfiguredPages';
				$list['Stabilization'] = 'Stabilization'; // unlisted
			}
		}
	}
}
