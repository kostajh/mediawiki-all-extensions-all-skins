<?php

namespace GrowthExperiments\HomepageModules;

use Config;
use GrowthExperiments\EditInfoService;
use GrowthExperiments\ExperimentUserManager;
use GrowthExperiments\HomepageModule;
use GrowthExperiments\HomepageModules\SuggestedEditsComponents\CardWrapper;
use GrowthExperiments\HomepageModules\SuggestedEditsComponents\TaskExplanationWidget;
use GrowthExperiments\NewcomerTasks\ConfigurationLoader\ConfigurationLoader;
use GrowthExperiments\NewcomerTasks\ConfigurationLoader\PageConfigurationLoader;
use GrowthExperiments\NewcomerTasks\NewcomerTasksUserOptionsLookup;
use GrowthExperiments\NewcomerTasks\ProtectionFilter;
use GrowthExperiments\NewcomerTasks\Task\TaskSet;
use GrowthExperiments\NewcomerTasks\Task\TemplateBasedTask;
use GrowthExperiments\NewcomerTasks\TaskSuggester\TaskSuggester;
use GrowthExperiments\NewcomerTasks\TaskType\TaskType;
use GrowthExperiments\NewcomerTasks\Topic\Topic;
use Html;
use IContextSource;
use MediaWiki\Extensions\PageViewInfo\PageViewService;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use Message;
use OOUI\ButtonGroupWidget;
use OOUI\ButtonWidget;
use OOUI\Exception;
use OOUI\HtmlSnippet;
use OOUI\IconWidget;
use OOUI\Tag;
use Status;
use StatusValue;
use TitleFactory;

/**
 * Homepage module that displays a list of recommended tasks.
 * This is JS-only functionality; most of the logic is in the
 * ext.growthExperiments.Homepage.SuggestedEdits module.
 */
class SuggestedEdits extends BaseModule {

	const ENABLED_PREF = 'growthexperiments-homepage-suggestededits';
	const ACTIVATED_PREF = 'growthexperiments-homepage-suggestededits-activated';
	const PREACTIVATED_PREF = 'growthexperiments-homepage-suggestededits-preactivated';
	public const TOPICS_PREF = 'growthexperiments-homepage-se-topic-filters';
	public const TOPICS_ORES_PREF = 'growthexperiments-homepage-se-ores-topic-filters';
	public const TOPICS_ENABLED_PREF = 'growthexperiments-homepage-suggestededits-topics-enabled';
	public const TASKTYPES_PREF = 'growthexperiments-homepage-se-filters';
	public const GUIDANCE_ENABLED_PREF = 'growthexperiments-guidance-enabled';
	public const DEFAULT_TASK_TYPES = [ 'copyedit', 'links' ];

	/**
	 * Used to keep track of the state of user interactions with suggested edits per type per skin.
	 * See also HomepageHooks::onLocalUserCreated
	 */
	public const GUIDANCE_BLUE_DOT_PREF =
		'growthexperiments-homepage-suggestededits-guidance-blue-dot';

	/**
	 * Change tag used to track edits made via the suggested edits interface. Some edits get a
	 * more specific tag instead.
	 */
	public const SUGGESTED_EDIT_TAG = 'newcomer task';
	/**
	 * Change tag used to track edits made via the Add Link structured edit task type.
	 */
	public const ADD_LINK_TAG = 'newcomer task add link';

	/** @var EditInfoService */
	private $editInfoService;

	/** @var ExperimentUserManager */
	private $experimentUserManager;

	/** @var PageViewService|null */
	private $pageViewService;

	/** @var ConfigurationLoader */
	private $configurationLoader;

	/** @var NewcomerTasksUserOptionsLookup */
	private $newcomerTasksUserOptionsLookup;

	/** @var TaskSuggester */
	private $taskSuggester;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var ProtectionFilter */
	private $protectionFilter;

	/** @var string[] cache key => HTML */
	private $htmlCache = [];

	/** @var TaskSet|StatusValue */
	private $tasks;

	/** @var int */
	private $unfilteredTasksetCount = null;

	/** @var ButtonGroupWidget */
	private $buttonGroupWidget;

	/**
	 * @param IContextSource $context
	 * @param EditInfoService $editInfoService
	 * @param ExperimentUserManager $experimentUserManager
	 * @param PageViewService|null $pageViewService
	 * @param ConfigurationLoader $configurationLoader
	 * @param NewcomerTasksUserOptionsLookup $newcomerTasksUserOptionsLookup
	 * @param TaskSuggester $taskSuggester
	 * @param TitleFactory $titleFactory
	 * @param ProtectionFilter $protectionFilter
	 */
	public function __construct(
		IContextSource $context,
		EditInfoService $editInfoService,
		ExperimentUserManager $experimentUserManager,
		?PageViewService $pageViewService,
		ConfigurationLoader $configurationLoader,
		NewcomerTasksUserOptionsLookup $newcomerTasksUserOptionsLookup,
		TaskSuggester $taskSuggester,
		TitleFactory $titleFactory,
		ProtectionFilter $protectionFilter
	) {
		parent::__construct( 'suggested-edits', $context, $experimentUserManager );
		$this->editInfoService = $editInfoService;
		$this->experimentUserManager = $experimentUserManager;
		$this->pageViewService = $pageViewService;
		$this->configurationLoader = $configurationLoader;
		$this->newcomerTasksUserOptionsLookup = $newcomerTasksUserOptionsLookup;
		$this->taskSuggester = $taskSuggester;
		$this->titleFactory = $titleFactory;
		$this->protectionFilter = $protectionFilter;
	}

	/** @inheritDoc */
	protected function getHeaderTextElement() {
		$context = $this->getContext();
		if ( $this->getMode() === self::RENDER_DESKTOP &&
			$this->experimentUserManager->isUserInVariant(
				$context->getUser(), [ 'C' ]
			)
		) {
			return Html::element(
					'div',
					[ 'class' => self::BASE_CSS_CLASS . '-header-text' ],
					$this->getHeaderText() ) .
				new ButtonWidget( [
					'id' => 'mw-ge-homepage-suggestededits-info',
					'icon' => 'info-unpadded',
					'framed' => false,
					'title' => $context->msg( 'growthexperiments-homepage-suggestededits-more-info' )->text(),
					'label' => $context->msg( 'growthexperiments-homepage-suggestededits-more-info' )->text(),
					'invisibleLabel' => true,
					'infusable' => true,
				] );
		} else {
			return parent::getHeaderTextElement();
		}
	}

	/**
	 * Check whether the suggested edits feature is (or could be) enabled for anyone
	 * on the wiki.
	 * @param Config $config
	 * @return bool
	 */
	public static function isEnabledForAnyone( Config $config ) {
		return $config->get( 'GEHomepageSuggestedEditsEnabled' );
	}

	/**
	 * Check whether the suggested edits feature is enabled for the context user.
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function isEnabled( IContextSource $context ) {
		return self::isEnabledForAnyone( $context->getConfig() ) && (
			!$context->getConfig()->get( 'GEHomepageSuggestedEditsRequiresOptIn' ) ||
			$context->getUser()->getBoolOption( self::ENABLED_PREF )
		);
	}

	/** @inheritDoc */
	public function getCssClasses() {
		return array_merge( parent::getCssClasses(),
			$this->getContext()->getUser()->getOption( self::ACTIVATED_PREF ) ?
				[ 'activated' ] :
				[ 'unactivated' ]
		);
	}

	/**
	 * Check whether topic matching has been enabled for the context user.
	 * Note that even with topic matching disabled, all the relevant backend functionality
	 * should still work (but logging and UI will be different).
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function isTopicMatchingEnabled( IContextSource $context ) {
		return self::isEnabled( $context ) &&
			$context->getConfig()->get( 'GEHomepageSuggestedEditsEnableTopics' ) && (
				!$context->getConfig()->get( 'GEHomepageSuggestedEditsTopicsRequiresOptIn' ) ||
				$context->getUser()->getBoolOption( self::TOPICS_ENABLED_PREF )
			);
	}

	/**
	 * Get the name of the preference to use for storing topic filters.
	 * @param Config $config
	 * @return string
	 */
	public static function getTopicFiltersPref( Config $config ) {
		$topicType = $config->get( 'GENewcomerTasksTopicType' );
		if ( $topicType === PageConfigurationLoader::CONFIGURATION_TYPE_ORES ) {
			return self::TOPICS_ORES_PREF;
		}
		return self::TOPICS_PREF;
	}

	/**
	 * Check if guidance feature is enabled for suggested edits.
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function isGuidanceEnabledForAnyone( IContextSource $context ) :bool {
		return $context->getConfig()->get( 'GENewcomerTasksGuidanceEnabled' );
	}

	/**
	 * Check if guidance feature is enabled for suggested edits.
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function isGuidanceEnabled( IContextSource $context ) :bool {
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		return self::isGuidanceEnabledForAnyone( $context ) && (
			!$context->getConfig()->get( 'GENewcomerTasksGuidanceRequiresOptIn' ) ||
			$userOptionsLookup->getBoolOption( $context->getUser(), self::GUIDANCE_ENABLED_PREF ) );
	}

	/** @inheritDoc */
	public function getHtml() {
		// This method will be called both directly by the homepage and by getJsData() in
		// some cases, so use some lightweight caching.
		$key = $this->getMode() . ':' . $this->getContext()->getLanguage()->getCode();
		if ( !array_key_exists( $key, $this->htmlCache ) ) {
			$this->htmlCache[$key] = parent::getHtml();
		}
		return $this->htmlCache[$key];
	}

	/** @inheritDoc */
	public function getJsData( $mode ) {
		$data = parent::getJsData( $mode );
		$data['task-preview'] = [];

		// Preload one task card for users who have the module activated
		if ( $this->canRender() && self::isActivated( $this->getContext() ) ) {
			$tasks = $this->getTaskSet();
			if ( $tasks instanceof StatusValue ) {
				$data['task-preview'] = [ 'error' => Status::wrap( $tasks )->getMessage()->parse() ];
			} elseif ( $tasks->count() === 0 ) {
				$data['task-preview'] = [ 'noresults' => true ];
			} else {
				$task = $tasks[0];
				$templates = null;
				if ( $task instanceof TemplateBasedTask ) {
					$templates = array_map( function ( LinkTarget $template ) {
						return $template->getText();
					}, $task->getTemplates() );
				}
				$title = $this->titleFactory->newFromLinkTarget( $task->getTitle() );
				$data['task-preview'] = [
					'tasktype' => $task->getTaskType()->getId(),
					'difficulty' => $task->getTaskType()->getDifficulty(),
					'title' => $title->getPrefixedText(),
					'topics' => $task->getTopicScores(),
					'maintenanceTemplates' => $templates,
					// The front-end code for constructing SuggestedEditCardWidget checks
					// to see if pageId is set in order to construct a tracking URL.
					'pageId' => $title->getArticleID()
				];
			}
		}

		// When the module is not activated yet, but can be, include module HTML in the
		// data, for dynamic loading on activation.
		if ( $this->canRender() &&
			!self::isActivated( $this->getContext() ) &&
			$this->getMode() !== HomepageModule::RENDER_MOBILE_DETAILS
		) {
			$data += [
				'html' => $this->getHtml(),
				'rlModules' => $this->getModules(),
			];
		}

		return $data;
	}

	/**
	 * Check whether suggested edits have been activated for the context user.
	 * Before activation, suggested edits are exposed via the StartEditing module;
	 * after activation (which happens by interacting with that module) via this one.
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function isActivated( IContextSource $context ) {
		return (bool)$context->getUser()->getBoolOption( self::ACTIVATED_PREF );
	}

	/** @inheritDoc */
	public function getState() {
		return self::isActivated( $this->getContext() ) ?
			self::MODULE_STATE_ACTIVATED :
			self::MODULE_STATE_UNACTIVATED;
	}

	/**
	 * Get a suggested task set, with in-process caching.
	 * @return TaskSet|StatusValue
	 */
	private function getTaskSet() {
		if ( $this->tasks ) {
			return $this->tasks;
		}
		$user = $this->getContext()->getUser();
		// There will likely be a cached task set by this point. For scenarios where there
		// aren't (e.g. user visits homepage, doesn't come back for 8 days, then goes to
		// homepage again), we should fetch tasks using a single task type and topic to
		// speed up the query.
		$taskTypes = $this->newcomerTasksUserOptionsLookup->getTaskTypeFilter( $user );
		$topics = $this->newcomerTasksUserOptionsLookup->getTopicFilter( $user );
		$tasks = $this->taskSuggester->suggest( $user, $taskTypes, $topics );
		if ( $tasks instanceof TaskSet ) {
			$this->unfilteredTasksetCount = $tasks->count();
			$tasks = $this->protectionFilter->filter( $tasks, 1 );
		}
		$this->tasks = $tasks;
		return $this->tasks;
	}

	/** @inheritDoc */
	protected function canRender() {
		return self::isEnabled( $this->getContext() )
			&& !$this->configurationLoader->loadTaskTypes() instanceof StatusValue;
	}

	/** @inheritDoc */
	protected function shouldRender() {
		return $this->canRender() && self::isActivated( $this->getContext() );
	}

	/** @inheritDoc */
	protected function getHeaderText() {
		return $this->getContext()
			->msg( 'growthexperiments-homepage-suggested-edits-header' )
			->text();
	}

	/** @inheritDoc */
	protected function getHeaderIconName() {
		return 'lightbulb';
	}

	/** @inheritDoc */
	protected function getBody() {
		$isDesktop = $this->getMode() === self::RENDER_DESKTOP;
		return Html::rawElement(
			'div', [ 'class' => 'suggested-edits-module-wrapper' ],
			( new Tag( 'div' ) )
				->addClasses( [ 'suggested-edits-filters' ] )
				->appendContent( $isDesktop ? $this->getFiltersButtonGroupWidget() : '' ) .
			( new Tag( 'div' ) )
				->addClasses( [ 'suggested-edits-pager' ] )
				->appendContent( $this->getPager() ) .
			( new CardWrapper(
				$this->getContext(),
				self::isTopicMatchingEnabled( $this->getContext() ),
				$this->getContext()->getLanguage()->getDir(),
				$this->getTaskSet()
			) )->render() .
			( new Tag( 'div' ) )->addClasses( [ 'suggested-edits-task-explanation' ] )
				->appendContent( ( new TaskExplanationWidget( [
					'taskSet' => $this->getTaskSet(),
					'localizer' => $this->getContext()
				] ) ) )
		);
	}

	/** @inheritDoc */
	protected function getFooter() {
		$siteViewsCount = $this->getSiteViews();
		$siteViewsMessage = $siteViewsCount ?
			$this->getContext()->msg( 'growthexperiments-homepage-suggestededits-footer' )
				->params( $this->formatSiteViews( $siteViewsCount ) ) :
			$this->getContext()->msg( 'growthexperiments-homepage-suggestededits-footer-noviews' );
		return $siteViewsMessage->parse();
	}

	/**
	 * @inheritDoc
	 * @suppress SecurityCheck-DoubleEscaped
	 */
	protected function getMobileSummaryBody() {
		$user = $this->getContext()->getUser();
		$showTaskPreview = $this->experimentUserManager->isUserInVariant( $user, [ 'C', 'D' ] )
			// If the task cannot be loaded, fall back to the old summary style for now.
			&& $this->getTaskSet() instanceof TaskSet
			&& $this->getTaskSet()->count() > 0;

		if ( $showTaskPreview ) {
			$taskPager = $this->getContext()->msg( 'growthexperiments-homepage-suggestededits-pager' )
				->numParams( 1, $this->getUnfilteredTaskSetCountReducedToTaskQueueLength() )
				->text();
			$button = new ButtonWidget( [
				'label' => $this->getContext()->msg(
					'growthexperiments-homepage-suggestededits-mobilesummary-footer-button' )->text(),
				'classes' => [ 'suggested-edits-preview-cta-button' ],
				'flags' => [ 'primary', 'progressive' ],
				// can't nest links
				'button' => new Tag( 'span' ),
			] );
			return Html::rawElement( 'div', [ 'class' => 'suggested-edits-main-with-preview' ],
				Html::rawElement( 'div', [ 'class' => 'suggested-edits-preview-pager' ], $taskPager )
					. $this->getTaskCard() . $button );
		} else {
			// For some reason phan thinks $siteEditsPerDay and/or $metricNumber get double-escaped,
			// but they are escaped just the right amount.
			$siteEditsPerDay = $this->editInfoService->getEditsPerDay();
			if ( $siteEditsPerDay instanceof StatusValue ) {
				LoggerFactory::getInstance( 'GrowthExperiments' )->warning(
					'Failed to load site edits per day stat: {status}',
					[ 'status' => Status::wrap( $siteEditsPerDay )->getWikiText( false, false, 'en' ) ]
				);
				// TODO probably have some kind of fallback message?
				$siteEditsPerDay = 0;
			}
			$metricNumber = $this->getContext()->getLanguage()->formatNum( $siteEditsPerDay );
			$metricSubtitle = $this->getContext()
				->msg( 'growthexperiments-homepage-suggestededits-mobilesummary-metricssubtitle' )
				->text();
			$footerText = $this->getContext()
				->msg( 'growthexperiments-homepage-suggestededits-mobilesummary-footer' )
				->text();
			return Html::rawElement( 'div', [ 'class' => 'suggested-edits-main' ],
					Html::rawElement( 'div', [ 'class' => 'suggested-edits-icon' ] ) .
					Html::rawElement( 'div', [ 'class' => 'suggested-edits-metric' ],
						Html::element( 'div', [ 'class' => 'suggested-edits-metric-number' ], $metricNumber ) .
						Html::element( 'div', [ 'class' => 'suggested-edits-metric-subtitle' ], $metricSubtitle )
					)
				) . Html::element( 'div', [
					'class' => 'suggested-edits-footer'
				], $footerText );
		}
	}

	/**
	 * Generate a button group widget with task and topic filters.
	 *
	 * This function should be kept in sync with
	 * SuggestedEditsFiltersWidget.prototype.updateButtonLabelAndIcon
	 * @return ButtonGroupWidget
	 */
	private function getFiltersButtonGroupWidget() : ButtonGroupWidget {
		$buttons = [];
		$user = $this->getContext()->getUser();
		if ( self::isTopicMatchingEnabled( $this->getContext() ) ) {
			// topicPreferences will be an empty array if the user had saved topics
			// in the past, or null if they have never saved topics
			$topicPreferences = $this->newcomerTasksUserOptionsLookup
				->getTopicFilterWithoutFallback( $user );
			$topicData = $this->configurationLoader->getTopics();
			$topicLabel = '';
			$addPulsatingDot = false;
			$flags = [];
			if ( !$topicPreferences ) {
				if ( $topicPreferences === null ) {
					$flags = [ 'progressive' ];
					$addPulsatingDot = true;
				}
				$topicLabel =
					$this->getContext()
						->msg( 'growthexperiments-homepage-suggestededits-topic-filter-select-interests' )
						->text();
			} else {
				$topicMessages = [];
				foreach ( $topicPreferences as $topicPreference ) {
					$topic = $topicData[$topicPreference] ?? null;
					if ( $topic instanceof Topic ) {
						$topicMessages[] = $topic->getName( $this->getContext() );
					}
				}
				array_filter( $topicMessages );
				if ( count( $topicMessages ) ) {
					if ( count( $topicMessages ) < 3 ) {
						$topicLabel =
							implode( $this->getContext()->msg( 'comma-separator' )->text(),
								$topicMessages );
					} else {
						$topicLabel =
							$this->getContext()
								->msg( 'growthexperiments-homepage-suggestededits-topics-button-topic-count' )
								->params( [
										$this->getContext()
											->getLanguage()
											->parseFormattedNumber( count( $topicMessages ) )
									] )
								->text();
					}
				}
			}

			$topicFilterButtonWidget = new ButtonWidget( [
				'label' => $topicLabel,
				'flags' => $flags,
				'classes' => [ 'topic-matching', 'topic-filter-button' ],
				'indicator' => $this->getMode() === self::RENDER_DESKTOP ? null : 'down',
				'icon' => 'funnel'
			] );
			if ( $addPulsatingDot ) {
				$topicFilterButtonWidget->appendContent(
					( new Tag( 'div' ) )->addClasses( [ 'mw-pulsating-dot' ] )
				);
			}
			$buttons[] = $topicFilterButtonWidget;
		}
		$difficultyFilterButtonWidget = new ButtonWidget( [
			'icon' => 'difficulty-outline',
			'classes' => self::isTopicMatchingEnabled( $this->getContext() ) ? [ 'topic-matching'
			] : [ '' ],
			'label' => $this->getContext()->msg(
				'growthexperiments-homepage-suggestededits-difficulty-filters-title'
			)->text(),
			'indicator' => $this->getMode() === self::RENDER_DESKTOP ? null : 'down'
		] );

		$levels = [];
		$taskTypeData = $this->configurationLoader->getTaskTypes();
		foreach ( $this->newcomerTasksUserOptionsLookup->getTaskTypeFilter( $user ) as $taskTypeId ) {
			/** @var TaskType $taskType */
			$taskType = $taskTypeData[$taskTypeId] ?? null;
			if ( $taskType ) {
				// Sometimes the default task types don't exist on a wiki (T268012)
				$levels[ $taskType->getDifficulty() ] = true;
			}
		}
		$taskTypeMessages = [];
		$messageKey = $this->getMode() === self::RENDER_DESKTOP ?
			'growthexperiments-homepage-suggestededits-difficulty-filter-label' :
			'growthexperiments-homepage-suggestededits-difficulty-filter-label-mobile';

		foreach ( [ 'easy', 'medium', 'hard' ] as $level ) {
			if ( !isset( $levels[$level] ) ) {
				continue;
			}
			// The following messages are used here:
			// * growthexperiments-homepage-suggestededits-difficulty-filter-label-easy
			// * growthexperiments-homepage-suggestededits-difficulty-filter-label-medium
			// * growthexperiments-homepage-suggestededits-difficulty-filter-label-hard
			$label = $this->getContext()->msg(
				'growthexperiments-homepage-suggestededits-difficulty-filter-label-' . $level
			);
			$message = $this->getContext()->msg( $messageKey )
				->params( $label )
				->text();
			$difficultyFilterButtonWidget->setLabel( $message );
			// Icons: difficulty-easy, difficulty-medium, difficulty-hard
			$difficultyFilterButtonWidget->setIcon( 'difficulty-' . $level );
			$taskTypeMessages[] = $label;
		}
		if ( count( $taskTypeMessages ) > 1 ) {
			$difficultyFilterButtonWidget->setIcon( 'difficulty-outline' );
			$messageKey = $this->getMode() === self::RENDER_DESKTOP ?
				'growthexperiments-homepage-suggestededits-difficulty-filter-label' :
				'growthexperiments-homepage-suggestededits-difficulty-filter-label-mobile';
			$message = $this->getContext()->msg( $messageKey )
				->params( implode( $this->getContext()->msg( 'comma-separator' ),
					$taskTypeMessages ) )
				->text();
			$difficultyFilterButtonWidget->setLabel( $message );
		}

		$buttons[] = $difficultyFilterButtonWidget;
		$this->buttonGroupWidget = new ButtonGroupWidget( [
			'class' => 'suggested-edits-filters',
			'items' => $buttons,
			'infusable' => true,
		] );
		return $this->buttonGroupWidget;
	}

	/**
	 * Generate HTML identical to that of mw.libs.ge.SmallTaskCard
	 * @return string
	 */
	private function getTaskCard() {
		$tasks = $this->getTaskSet();
		$task = $tasks[0];
		$title = $this->titleFactory->newFromLinkTarget( $task->getTitle() );

		$image = Html::element( 'div',
			[ 'class' => 'mw-ge-small-task-card-image mw-ge-small-task-card-image-skeleton' ] );
		$title = Html::element( 'span',
			[ 'class' => 'mw-ge-small-task-card-title' ],
			$title->getPrefixedText() );
		$description = Html::element( 'div',
			[ 'class' => 'mw-ge-small-task-card-description skeleton' ] );
		$taskIcon = new IconWidget( [ 'icon' => 'difficulty-' . $task->getTaskType()->getDifficulty() ] );
		$taskType = Html::rawElement( 'span',
			[ 'class' => 'mw-ge-small-task-card-tasktype '
				 // The following classes are used here:
				 // * mw-ge-small-task-card-tasktype-difficulty-easy
				 // * mw-ge-small-task-card-tasktype-difficulty-medium
				 // * mw-ge-small-task-card-tasktype-difficulty-hard
				. 'mw-ge-small-task-card-tasktype-difficulty-' . $task->getTaskType()->getDifficulty() ],
			$taskIcon . $task->getTaskType()->getName( $this->getContext() ) );

		$glue = Html::element( 'div',
			[ 'class' => 'mw-ge-small-task-card-glue' ] );
		$cardMetadataContainer = Html::rawElement( 'div',
			[ 'class' => 'mw-ge-small-task-card-metadata-container' ],
			// Unlike SmallTaskCard, this version does not have pageviews.
			$taskType );
		$cardTextContainer = Html::rawElement( 'div',
			[ 'class' => 'mw-ge-small-task-card-text-container' ],
			$title . $description . $glue . $cardMetadataContainer );
		return Html::rawElement( 'div',
			// only called for mobile views
			[ 'class' => 'mw-ge-small-task-card mw-ge-small-task-card-mobile' ],
		$image . $cardTextContainer );
	}

	/** @inheritDoc */
	protected function getSubheader() {
		// Ugly hack to get the filters positioned outside of the module wrapper on mobile.
		$mobileDetails = [ self::RENDER_MOBILE_DETAILS, self::RENDER_MOBILE_DETAILS_OVERLAY ];
		if ( !in_array( $this->getMode(), $mobileDetails, true ) ) {
			return '';
		}
		return Html::rawElement( 'div', [ 'class' => 'suggested-edits-filters' ] );
	}

	/** @inheritDoc */
	protected function getSubheaderTag() {
		return 'div';
	}

	/** @inheritDoc */
	protected function getModuleStyles() {
		return array_merge(
			parent::getModuleStyles(),
			[ 'mediawiki.pulsatingdot' ]
		);
	}

	/** @inheritDoc */
	protected function getModules() {
		$variantC = $this->experimentUserManager->isUserInVariant(
			$this->getContext()->getUser(), [ 'C' ]
		);
		return array_merge(
			parent::getModules(),
			[ 'ext.growthExperiments.Homepage.SuggestedEdits' ],
			// The code to infuse the info button is in the StartEditing module
			$variantC ? [ 'ext.growthExperiments.Homepage.StartEditing' ] : []
		);
	}

	/**
	 * Returns daily unique site views, averaged over the last 30 days.
	 * @return int|null
	 */
	protected function getSiteViews() {
		if ( !$this->pageViewService ||
			 !$this->pageViewService->supports( PageViewService::METRIC_UNIQUE, PageViewService::SCOPE_SITE )
		) {
			return null;
		}
		// When PageViewService is a WikimediaPageViewService, the pageviews for the last two days
		// or so will be missing due to AQS processing lag. Get some more days and discard the
		// newest ones.
		$status = $this->pageViewService->getSiteData( 32, PageViewService::METRIC_UNIQUE );
		if ( !$status->isOK() ) {
			return null;
		}
		$data = $status->getValue();
		ksort( $data );
		return (int)( array_sum( array_slice( $data, 0, 30 ) ) / 30 );
	}

	/**
	 * Format site views count in a human-readable way.
	 * @param int $siteViewsCount
	 * @return string|array A Message::params() parameter
	 */
	protected function formatSiteViews( int $siteViewsCount ) {
		// We only get here when $siteViewsCount is not 0 so log is safe.
		$siteViewsCount = (int)round( $siteViewsCount, -floor( log10( $siteViewsCount ) ) );
		$language = $this->getContext()->getLanguage();
		if ( $this->getContext()->msg( 'growthexperiments-homepage-suggestededits-footer-suffix' )
			->isDisabled()
		) {
			// This language does not use suffixes, just output the rounded number
			return Message::numParam( $siteViewsCount );
		}
		// Abuse Language::formatComputingNumbers into displaying large numbers in a human-readable way
		return $language->formatComputingNumbers( $siteViewsCount, 1000,
			'growthexperiments-homepage-suggestededits-footer-$1suffix' );
	}

	/** @inheritDoc */
	protected function getActionData() {
		$user = $this->getContext()->getUser();
		$taskSet = $this->getTaskSet();
		$topics = null;
		if ( $taskSet instanceof TaskSet ) {
			$taskTypes = $taskSet->getFilters()->getTaskTypeFilters();
			$topics = $taskSet->getFilters()->getTopicFilters();
		}
		// these will be updated on the client side as needed
		$data = [
			'taskTypes' => $taskTypes ?? $this->newcomerTasksUserOptionsLookup->getTaskTypeFilter( $user ),
			'taskCount' => $this->getUnfilteredTaskSetCountReducedToTaskQueueLength()
		];
		if ( self::isTopicMatchingEnabled( $this->getContext() ) ) {
			$data['topics'] = $topics ?? $this->newcomerTasksUserOptionsLookup->getTopicFilter( $user );
		}
		return array_merge( parent::getActionData(), $data );
	}

	/**
	 * @inheritDoc
	 */
	protected function getJsConfigVars() {
		return [
			'GEHomepageSuggestedEditsEnableTopics' => self::isTopicMatchingEnabled( $this->getContext() )
		];
	}

	/**
	 * Get the pager text (1 of X) to show on server side render.
	 *
	 * This code roughly corresponds to SuggestedEditPagerWidget.prototype.setMessage
	 *
	 * @return string
	 * @throws Exception
	 */
	private function getPager() {
		$taskSet = $this->getTaskSet();
		if ( !$taskSet instanceof TaskSet || !$taskSet->count() ) {
			return '';
		}
		return new HtmlSnippet( $this->getContext()->msg( 'growthexperiments-homepage-suggestededits-pager' )
				->params( [ 1, $this->getUnfilteredTaskSetCountReducedToTaskQueueLength() ] )
			->parse() );
	}

	/**
	 * The max number on the client-side is 200 (TASK_QUEUE_LENGTH in SuggestedEdits.js), while
	 * we can have up to 250 stored in the cache. Reduce the number to TASK_QUEUE_LENGTH
	 * if it's over that limit.
	 * @return int
	 */
	private function getUnfilteredTaskSetCountReducedToTaskQueueLength() : int {
		return min( $this->unfilteredTasksetCount ?? 0, 200 );
	}

}
