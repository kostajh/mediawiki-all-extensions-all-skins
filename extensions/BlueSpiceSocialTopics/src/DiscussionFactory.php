<?php

namespace BlueSpice\Social\Topics;

use BlueSpice\Context;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Entity;
use BlueSpice\EntityFactory;
use BlueSpice\Social\Data\Entity\Store;
use BlueSpice\Social\Topics\Entity\Discussion;
use BlueSpice\Social\Topics\EntityListContext\SpecialDiscussions;
use MediaWiki\MediaWikiServices;
use MWException;
use Title;

class DiscussionFactory extends EntityFactory {

	/**
	 *
	 * @var Discussion[]
	 */
	protected $discussionInstances = [];

	/**
	 * @param Title $title
	 * @return Discussion | null
	 */
	public function newFromDiscussionTitle( Title $title ) {
		if ( !$title->exists() ) {
			return null;
		}

		if ( !$title->isTalkPage() ) {
			$title = $title->getTalkPage();
			if ( !$title instanceof Title || !$title->exists() ) {
				return null;
			}
		}
		if ( isset( $this->discussionInstances[$title->getArticleID()] ) ) {
			return $this->discussionInstances[$title->getArticleID()];
		}

		$context = new Context(
			\RequestContext::getMain(),
			$this->config
		);
		$serviceUser = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();

		$listContext = new SpecialDiscussions(
			$context,
			$context->getConfig(),
			$serviceUser,
			null
		);
		$filters = $listContext->getFilters();
		$filters[] = (object)[
			Numeric::KEY_PROPERTY => Discussion::ATTR_DISCUSSION_TITLE_ID,
			Numeric::KEY_VALUE => $title->getArticleID(),
			Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
			Numeric::KEY_TYPE => 'numeric'
		];

		$instance = null;
		$params = new ReaderParams( [
			'filter' => $filters,
			'sort' => $listContext->getSort(),
			'limit' => 1,
			'start' => 0,
		] );
		$res = $this->getStore()->getReader( $listContext )->read( $params );
		foreach ( $res->getRecords() as $row ) {
			$instance = $this->newFromObject( $row->getData() );
		}
		if ( !$instance ) {
			$instance = $this->newFromObject( (object)[
				Discussion::ATTR_TYPE => Discussion::TYPE,
				Discussion::ATTR_DISCUSSION_TITLE_ID => $title->getArticleID(),
			] );
		}
		$this->discussionInstances[$title->getArticleID()] = $instance;
		return $instance;
	}

	/**
	 *
	 * @return Store
	 * @throws MWException
	 */
	protected function getStore() {
		$config = $this->configFactory->newFromType( Discussion::TYPE );
		$storeClass = $config->get( 'StoreClass' );
		if ( !class_exists( $storeClass ) ) {
			throw new MWException( "Store class '$storeClass' not found" );
		}
		return new $storeClass();
	}

	/**
	 *
	 * @param Entity &$instance
	 * @return Entity
	 */
	public function detachCache( Entity &$instance ) {
		$id = $instance->get( Discussion::ATTR_DISCUSSION_TITLE_ID, 0 );
		if ( isset( $this->discussionInstances[$id] ) ) {
			unset( $this->discussionInstances[$id] );
		}
		return parent::detachCache( $instance );
	}
}
