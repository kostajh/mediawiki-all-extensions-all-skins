<?php

namespace BlueSpice\Social\WikiPage\EntityListContext;

use BlueSpice\Data\Filter\Boolean;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Social\WikiPage\Entity\Stash;
use Config;
use HtmlArmor;
use IContextSource;
use MediaWiki\MediaWikiServices;
use Message;
use SpecialPage;
use Title;
use User;

class AfterContent extends EntityListContext {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param User|null $user
	 * @param Entity|null $entity
	 * @param Title|null $title
	 */
	public function __construct( IContextSource $context, Config $config,
		User $user = null, Entity $entity = null, Title $title = null ) {
		parent::__construct( $context, $config, $user, $entity );
		if ( $title ) {
			$this->title = $title;
		}
	}

	/**
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->title ? $this->title : $this->context->getTitle();
	}

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 999;
	}

	/**
	 *
	 * @return string
	 */
	public function getSortProperty() {
		return Stash::ATTR_TIMESTAMP_TOUCHED;
	}

	/**
	 *
	 * @return bool
	 */
	public function useEndlessScroll() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function useMoreScroll() {
		return false;
	}

	/**
	 *
	 * @return array
	 */
	public function getLockedFilterNames() {
		return array_merge(
			parent::getLockedFilterNames(),
			[ Stash::ATTR_TYPE ]
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getOutputTypes() {
		return array_merge(
			parent::getOutputTypes(),
			[ Stash::TYPE => 'Default' ]
		);
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getStashTitleIDFilter() {
		return (object)[
			Numeric::KEY_PROPERTY => Stash::ATTR_WIKI_PAGE_ID,
			Numeric::KEY_VALUE => $this->getTitle()->getArticleID(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getArchiveFilter() {
		return (object)[
			Boolean::KEY_PROPERTY => Stash::ATTR_ARCHIVED,
			Boolean::KEY_VALUE => false,
			Boolean::KEY_COMPARISON => Boolean::COMPARISON_EQUALS,
			Boolean::KEY_TYPE => 'boolean'
		];
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getTypeFilter() {
		return (object)[
			ListValue::KEY_PROPERTY => Stash::ATTR_TYPE,
			ListValue::KEY_VALUE => [ Stash::TYPE ],
			ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
			ListValue::KEY_TYPE => \BlueSpice\Data\FieldType::LISTVALUE
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getFilters() {
		return array_merge( parent::getFilters(),
			[
				$this->getStashTitleIDFilter(),
				$this->getArchiveFilter()
			]
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getMoreLink() {
		$special = SpecialPage::getTitleFor(
			'WikiPageStash',
			$this->getTitle()->getFullText()
		);
		return MediaWikiServices::getInstance()->getLinkRenderer()->makeKnownLink(
			$special,
			new HtmlArmor( $this->getMoreLinkMessage()->text() )
		);
	}

	/**
	 *
	 * @return Message
	 */
	protected function getMoreLinkMessage() {
		return $this->context->msg( 'bs-social-entitylistmore-linklabel' );
	}

	/**
	 *
	 * @return Entity
	 */
	public function showEntityListMore() {
		return $this->entity && $this->entity->exists();
	}

	/**
	 *
	 * @return array
	 */
	public function getPreloadedEntities() {
		$preloaded = parent::getPreloadedEntities();
		$stash = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromObject(
			$this->getRawStash()
		);
		if ( !$stash instanceof Stash ) {
			return $preloaded;
		}

		$status = $stash->userCan( 'create', $this->getUser() );
		if ( !$status->isOK() ) {
			return $preloaded;
		}

		$preloaded[] = $this->getRawStash();
		return $preloaded;
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getRawStash() {
		$talkPage = $this->getTitle();
		return (object)[
			Stash::ATTR_TYPE => Stash::TYPE,
			Stash::ATTR_WIKI_PAGE_ID => $talkPage->getArticleID(),
			Stash::ATTR_RELATED_TITLE => $talkPage->getFullText(),
		];
	}

	/**
	 *
	 * @return bool
	 */
	public function showEntityListMenu() {
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function showHeadline() {
		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getHeadlineMessageKey() {
		return 'bs-socialwikipage-aftercontent-heading';
	}

	/**
	 * Returns the key for the renderer, that initialy is used
	 * @return string
	 */
	public function getRendererName() {
		if ( !$this->entity || !$this->entity->exists() ) {
			return 'social-wikipage-entitylist-newwikipageentity';
		}
		return 'social-wikipage-entitylist-attachments';
	}
}
