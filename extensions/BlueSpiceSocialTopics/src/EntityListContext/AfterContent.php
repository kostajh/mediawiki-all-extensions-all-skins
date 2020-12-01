<?php

namespace BlueSpice\Social\Topics\EntityListContext;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Topics\Entity\Topic;
use Config;
use HtmlArmor;
use IContextSource;
use MediaWiki\MediaWikiServices;
use Title;
use User;

class AfterContent extends \BlueSpice\Social\EntityListContext {

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
	public function __construct( IContextSource $context, Config $config, User $user = null,
		Entity $entity = null, Title $title = null ) {
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
		return 3;
	}

	/**
	 *
	 * @return string
	 */
	public function getSortProperty() {
		return Topic::ATTR_TIMESTAMP_TOUCHED;
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
			[ Topic::ATTR_TYPE ]
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getOutputTypes() {
		return array_merge( parent::getOutputTypes(), [ Topic::TYPE => 'Default' ] );
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getDiscussionTitleIDFilter() {
		return (object)[
			Numeric::KEY_PROPERTY => Topic::ATTR_DISCUSSION_TITLE_ID,
			Numeric::KEY_VALUE => $this->getTitle()->getTalkPage()->getArticleID(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getTypeFilter() {
		return (object)[
			ListValue::KEY_PROPERTY => Topic::ATTR_TYPE,
			ListValue::KEY_VALUE => [ Topic::TYPE ],
			ListValue::KEY_COMPARISON => ListValue::COMPARISON_CONTAINS,
			ListValue::KEY_TYPE => FieldType::LISTVALUE
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getFilters() {
		return array_merge( parent::getFilters(),
			[ $this->getDiscussionTitleIDFilter() ]
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getMoreLink() {
		return MediaWikiServices::getInstance()->getLinkRenderer()->makeKnownLink(
			$this->getTitle()->getTalkPage(),
			new HtmlArmor( $this->getMoreLinkMessage()->text() )
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getPreloadedEntities() {
		$preloaded = parent::getPreloadedEntities();
		$topic = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromObject(
			$this->getRawTopic()
		);
		if ( !$topic instanceof Topic ) {
			return $preloaded;
		}

		$status = $topic->userCan( 'create', $this->getUser() );
		if ( !$status->isOK() ) {
			return $preloaded;
		}

		$preloaded[] = $this->getRawTopic();
		return $preloaded;
	}

	/**
	 *
	 * @return \stdClass
	 */
	protected function getRawTopic() {
		$talkPage = $this->getTitle()->getTalkPage();
		return (object)[
			Topic::ATTR_TYPE => Topic::TYPE,
			Topic::ATTR_DISCUSSION_TITLE_ID => $talkPage->getArticleID(),
			Topic::ATTR_RELATED_TITLE => $talkPage->getFullText(),
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
		return 'bs-socialtopics-aftercontent-heading';
	}

	/**
	 * Returns the key for the renderer, that initialy is used
	 * @return string
	 */
	public function getRendererName() {
		if ( !$this->entity || !$this->entity->exists() ) {
			return 'social-topics-entitylist-newdiscussion';
		}
		return 'social-topics-entitylist-topicsaftercontent';
	}

	/**
	 *
	 * @return bool
	 */
	public function showEntityListMore() {
		return $this->entity && $this->entity->exists();
	}
}
