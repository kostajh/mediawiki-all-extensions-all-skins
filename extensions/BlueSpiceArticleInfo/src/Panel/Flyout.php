<?php

namespace BlueSpice\ArticleInfo\Panel;

use BlueSpice\Calumma\IFlyout;
use BlueSpice\Calumma\Panel\BasePanel;
use MediaWiki\MediaWikiServices;
use Message;
use QuickTemplate;
use WikiPage;

class Flyout extends BasePanel implements IFlyout {
	/**
	 * RL modules that are added by other extensions
	 * wanting to show their info in this flyout
	 *
	 * @var array
	 */
	protected $modulesToLoad = [];

	/**
	 * Callback function to allow other extesions to
	 * insert content
	 *
	 * @var array
	 */
	protected $makeItemsCallbacks = [];

	/**
	 * @var \Title
	 */
	protected $title;

	/**
	 * @var \MediaWiki\Storage\RevisionStore
	 */
	protected $revisionStore;

	/**
	 * @var \Article
	 */
	protected $article;

	/**
	 * @var WikiPage
	 */
	protected $wikiPage;

	/**
	 *
	 * @param QuickTemplate $skintemplate
	 */
	public function __construct( QuickTemplate $skintemplate ) {
		parent::__construct( $skintemplate );
		$this->setForeignModules();
		$this->title = $skintemplate->getSkin()->getTitle();
	}

	/**
	 * @return Message
	 */
	public function getFlyoutTitleMessage() {
		return $this->skintemplate->getSkin()->msg( 'bs-articleinfo-flyout-title' );
	}

	/**
	 * @return Message
	 */
	public function getFlyoutIntroMessage() {
		return $this->skintemplate->getSkin()->msg( 'bs-articleinfo-flyout-intro' );
	}

	/**
	 * @return Message
	 */
	public function getTitleMessage() {
		return $this->skintemplate->getSkin()->msg( 'bs-articleinfo-nav-link-title' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerData() {
		$data = [
			'make-items-callbacks' => \FormatJson::encode( $this->makeItemsCallbacks ),
			'rl-module-dependencies' => \FormatJson::encode( $this->modulesToLoad )
		];

		$lastEditedTime = $this->getLastEditedTime();
		if ( $lastEditedTime ) {
			$data['last-edited-time'] = $lastEditedTime;
		}

		$lastEditor = $this->getLastEditedUser();
		if ( $lastEditor ) {
			$data['last-edited-user'] = $lastEditor;
		}

		$categoryLinks = $this->getCategoryLinks();
		if ( $categoryLinks ) {
			$data['category-links'] = $categoryLinks;
		}

		$templateLinks = $this->getTemplateLinks();
		if ( $templateLinks ) {
			$data['template-links'] = $templateLinks;
		}

		$skin = $this->skintemplate->getSkin();
		$data['has-subpages'] = $skin->getTitle()->hasSubpages();

		$data['user-can-edit'] = MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $skin->getUser(), $skin->getTitle() );

		return $data;
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return 'bs.articleinfo.flyoutCallback';
	}

	/**
	 *
	 * @return array
	 */
	public function getTriggerRLDependencies() {
		return [ 'ext.bluespice.articleinfo.flyout' ];
	}

	/**
	 *
	 */
	protected function setForeignModules() {
		$flyoutModuleRegistry = \ExtensionRegistry::getInstance()
			->getAttribute( 'BlueSpiceArticleInfoFlyoutModules' );

		foreach ( $flyoutModuleRegistry as $key => $module ) {
			if ( isset( $module['skip-callback'] ) && is_callable( $module['skip-callback'] ) ) {
				$result = call_user_func_array(
					$module['skip-callback'],
					[ $this->skintemplate->getSkin()->getContext() ]
				);

				if ( !$result ) {
					continue;
				}
			}

			$this->modulesToLoad[] = $module['module'];
			$this->makeItemsCallbacks[] = $module['make-items-callback'];
		}
	}

	/**
	 *
	 * @return \MediaWiki\Storage\RevisionStore
	 */
	protected function getRevisionStore() {
		if ( $this->revisionStore === null ) {
			$this->revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		}
		return $this->revisionStore;
	}

	/**
	 * DEPRECATED
	 * @deprecated since version 3.3.0 - use $this->getWikiPage() instead
	 * @return \Article
	 */
	protected function getArticle() {
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		if ( $this->article === null ) {
			$this->article = \Article::newFromID( $this->title->getArticleID() );
		}
		return $this->article;
	}

	/**
	 *
	 * @return WikiPage
	 */
	protected function getWikiPage() {
		if ( $this->wikiPage === null ) {
			$this->wikiPage = WikiPage::factory( $this->title );
		}
		return $this->wikiPage;
	}

	/**
	 * Gets info on last time page was edited
	 *
	 * @return string|false if cannot be retrieved
	 */
	protected function getLastEditedTime() {
		$article = $this->getWikiPage();
		$oldId = $this->skintemplate->getSkin()->getRequest()->getInt( 'oldid', 0 );

		if ( !$article instanceof WikiPage ) {
			return false;
		}

		if ( $oldId != 0 ) {
			$this->getRevisionStore();
			$timestamp = $this->revisionStore->getTimestampFromId( $article->getTitle(), $oldId );
		} else {
			$timestamp = $article->getTimestamp();
		}

		$formattedTimestamp = ( new \BlueSpice\Timestamp( $timestamp ) )->getAgeString();
		$articleHistoryLinkURL = $article->getTitle()->getLinkURL(
			[
				'diff'   => 0,
				'oldid' => $oldId
			]
		);

		return \FormatJson::encode( [
			'timestamp' => $formattedTimestamp,
			'url' => $articleHistoryLinkURL
		] );
	}

	/**
	 * Gets info on last editor of the page
	 *
	 * @return string|false if cannot be retrieved
	 */
	protected function getLastEditedUser() {
		$article = $this->getWikiPage();
		if ( !$article instanceof WikiPage ) {
			return false;
		}

		$userText = $article->getUserText();
		if ( $userText == '' ) {
			return false;
		}

		$lastEditor = \User::newFromName( $userText );
		if ( $lastEditor instanceof \User === false ) {
			return false;
		}

		$userHelper = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getUserHelper( $lastEditor );

		return \FormatJson::encode( [
			'userText' => $userHelper->getDisplayName(),
			'url' => $lastEditor->getUserPage()->getFullURL()
		] );
	}

	/**
	 * Gets all categories of the page
	 * @return string
	 */
	protected function getCategoryLinks() {
		$allPageCategoryLinks = $this->skintemplate->getSkin()->getOutput()->getCategoryLinks();

		$pageCategoryLinks = [];
		if ( isset( $allPageCategoryLinks['normal'] ) ) {
			$pageCategoryLinks = $allPageCategoryLinks['normal'];
		}

		if ( $this->skintemplate->getSkin()->getUser()->getBoolOption( 'showhiddencats' ) ) {
			if ( isset( $allPageCategoryLinks['hidden'] ) ) {
				$pageCategoryLinks = array_merge(
					$pageCategoryLinks,
					$allPageCategoryLinks['hidden']
				);
			}
		}

		// This is just so that the ExtJS store can handle it more easily
		$keyedCategoryLinks = [];
		foreach ( $pageCategoryLinks as $categoryLink ) {
			$keyedCategoryLinks[] = [
				'category_anchor' => $categoryLink,
				'class' => 'pill'
			];
		}

		return \FormatJson::encode( $keyedCategoryLinks );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplateLinks() {
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$templateTitles = $this->title->getTemplateLinksFrom();

		$templateLinks = [];
		foreach ( $templateTitles as $title ) {
			$templateLinks[] = [
				'template_anchor' => $linkRenderer->makeLink( $title, $title->getText() )
			];
		}

		return \FormatJson::encode( $templateLinks );
	}
}
