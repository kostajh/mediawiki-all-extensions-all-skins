<?php

namespace BlueSpice\Calumma\Renderer\PageHeader;

use BlueSpice\Calumma\Renderer\PageHeader;
use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use BlueSpice\Timestamp;
use BlueSpice\UtilityFactory;
use Config;
use Exception;
use Html;
use HtmlArmor;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use QuickTemplate;
use RequestContext;
use WikiPage;

class LastEdit extends PageHeader {

	/**
	 *
	 * @var UtilityFactory
	 */
	protected $util = null;

	/**
	 *
	 * @var RevisionStore
	 */
	protected $revisionStore = null;

	/**
	 * LastEdit constructor.
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name
	 * @param QuickTemplate|null $skinTemplate
	 * @param UtilityFactory|null $util
	 * @param RevisionStore|null $revisionStore
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', QuickTemplate $skinTemplate = null, UtilityFactory $util = null,
		RevisionStore $revisionStore = null ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name, $skinTemplate );

		$this->util = $util;
		$this->revisionStore = $revisionStore;
	}

	/**
	 *
	 * @param string $name
	 * @param MediaWikiServices $services
	 * @param Config $config
	 * @param Params $params
	 * @param IContextSource|null $context
	 * @param LinkRenderer|null $linkRenderer
	 * @param QuickTemplate|null $skinTemplate
	 * @param UtilityFactory|null $util
	 * @param RevisionStore|null $revisionStore
	 * @return Renderer
	 */
	public static function factory( $name, MediaWikiServices $services, Config $config,
		Params $params, IContextSource $context = null, LinkRenderer $linkRenderer = null,
		QuickTemplate $skinTemplate = null, UtilityFactory $util = null,
		RevisionStore $revisionStore = null ) {
		if ( !$context ) {
			$context = $params->get(
				static::PARAM_CONTEXT,
				false
			);
			if ( !$context instanceof IContextSource ) {
				$context = RequestContext::getMain();
			}
		}
		if ( !$linkRenderer ) {
			$linkRenderer = $services->getLinkRenderer();
		}
		if ( !$util ) {
			$util = $services->getService( 'BSUtilityFactory' );
		}
		if ( !$skinTemplate ) {
			$skinTemplate = $params->get( static::SKIN_TEMPLATE, null );
		}
		if ( !$skinTemplate ) {
			throw new Exception(
				'Param "' . static::SKIN_TEMPLATE . '" must be an instance of '
				. QuickTemplate::class
			);
		}
		if ( !$revisionStore ) {
			$revisionStore = $services->getRevisionStore();
		}

		return new static(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$skinTemplate,
			$util,
			$revisionStore
		);
	}

	/**
	 * @return string
	 */
	public function render() {
		$html = '';
		$title = $this->getContext()->getTitle();
		if ( !$title || $title->isSpecialPage() ) {
			return $html;
		}

		$wikiPage = WikiPage::factory( $title );
		$currentRevision = $this->getCurrentRevision( $wikiPage );

		if ( !$currentRevision ) {
			return '';
		}

		$diffLink = $this->makeLastEditDiffLink( $wikiPage, $currentRevision );
		$userLink = $this->makeLastEditorLink( $wikiPage, $currentRevision );

		$lastEditText = $this->msg(
			'bs-calumma-page-last-edit-text',
			$diffLink,
			$userLink
		)->text();

		return Html::rawElement(
			'span',
			[ 'class' => "bs-page-last-edit" ],
			$lastEditText
		);
	}

	/**
	 *
	 * @param WikiPage $wikiPage
	 * @param RevisionRecord $currentRevision
	 * @return string
	 */
	protected function makeLastEditDiffLink( WikiPage $wikiPage, $currentRevision ) {
		$rawTimestamp = $currentRevision->getTimestamp();
		$formattedDate = $this->getContext()->getLanguage()->date( $rawTimestamp );

		$revisionTimestamp = Timestamp::getInstance( $rawTimestamp );
		$formattedPeriod = $revisionTimestamp->getAgeString( null, null, 1 );

		$diffLink = $this->linkRenderer->makeLink(
			$wikiPage->getTitle(),
			new HtmlArmor( $formattedPeriod ),
			[
				'title' => $formattedDate
			],
			[
				'oldid' => $currentRevision->getId(),
				'diff' => 'prev'
			]
		);

		return $diffLink;
	}

	/**
	 *
	 * @param WikiPage $wikiPage
	 * @param RevisionRecord $currentRevision
	 * @return string
	 */
	protected function makeLastEditorLink( WikiPage $wikiPage, $currentRevision ) {
		$userIdentity = $currentRevision->getUser();

		/* Main_page is created with user id 0 */
		if ( !$userIdentity || $userIdentity->getId() === 0 ) {
			return $this->msg( 'bs-calumma-page-last-edit-by-system-user' )->plain();
		}

		$userLink = $this->linkRenderer->makeLink(
			$userIdentity->getUserPage(),
			$this->util->getUserHelper( $userIdentity )->getDisplayName()
		);

		return $userLink;
	}

	/**
	 * @param WikiPage $wikiPage
	 * @return RevisionRecord|null
	 */
	protected function getCurrentRevision( WikiPage $wikiPage ) {
		return $this->revisionStore->getRevisionByTitle( $wikiPage->getTitle() );
	}
}
