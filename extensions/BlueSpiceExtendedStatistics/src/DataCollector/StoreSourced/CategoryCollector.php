<?php

namespace BlueSpice\ExtendedStatistics\DataCollector\StoreSourced;

use BlueSpice\Data\Categories\Store as CategoryStore;
use BlueSpice\Data\IStore;
use BlueSpice\Data\ReaderParams;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use Category;
use Config;
use LoadBalancer;
use MediaWiki\MediaWikiServices;
use MWException;
use RequestContext;
use ResultWrapper;
use Title;

abstract class CategoryCollector extends SnapshotDiffCollector {

	/**
	 *
	 * @var CategoryStore
	 */
	protected $categoryStore = null;

	/**
	 * @var LoadBalancer
	 */
	protected $lb = null;

	/**
	 * @var Category[]
	 */
	protected $validCategories = [];

	/**
	 * @var ResultWrapper
	 */
	protected $categoryLinks;

	/**
	 * Helper method
	 *
	 * @param MediaWikiServices $services
	 * @return CategoryStore
	 * @throws MWException
	 */
	public static function getCategoryStore( MediaWikiServices $services ) {
		$context = RequestContext::getMain();
		$context->setUser(
			$services->getService( 'BSUtilityFactory' )->getMaintenanceUser()->getUser()
		);
		return new CategoryStore( $context );
	}

	/**
	 *
	 * @param string $type
	 * @param Snapshot $snapshot
	 * @param Config $config
	 * @param EntityFactory $factory
	 * @param IStore $store
	 * @param SnapshotFactory $snapshotFactory
	 * @param CategoryStore $categoryStore
	 * @param LoadBalancer $lb
	 */
	protected function __construct( $type, Snapshot $snapshot, Config $config,
		EntityFactory $factory, IStore $store, SnapshotFactory $snapshotFactory,
		CategoryStore $categoryStore, LoadBalancer $lb ) {
		parent::__construct( $type, $snapshot, $config, $factory, $store, $snapshotFactory );
		$this->categoryStore = $categoryStore;
		$this->lb = $lb;
	}

	/**
	 * Get all valid categories
	 * Cached for performance
	 *
	 * @return Category[]
	 */
	protected function getValidCategories() {
		if ( !$this->validCategories ) {
			$res = $this->readCategoryStore();
			foreach ( $res->getRecords() as $record ) {
				$category = Category::newFromRow( $record->getData() );
				if ( $category instanceof Category ) {
					$this->validCategories[$category->getTitle()->getDBkey()] = $category;
				}
			}
		}

		return $this->validCategories;
	}

	/**
	 *
	 * @param Title $title
	 * @return array
	 */
	protected function getCategoriesForTitle( Title $title ) {
		if ( !$title instanceof Title ) {
			return [];
		}
		$this->getCategoryLinks();

		$titleCategories = [];
		foreach ( $this->categoryLinks as $row ) {
			if ( (int)$row->cl_from !== $title->getArticleID() ) {
				continue;
			}
			if ( !isset( $this->validCategories[$row->cl_to] ) ) {
				continue;
			}
			$titleCategories[$row->cl_to] = $this->validCategories[$row->cl_to];
		}
		ksort( $titleCategories );
		return $titleCategories;
	}

	private function getCategoryLinks() {
		if ( !$this->categoryLinks ) {
			$db = $this->lb->getConnection( DB_REPLICA );
			$this->categoryLinks = $db->select(
				'categorylinks',
				[ 'cl_to', 'cl_from', 'cl_type' ],
				[],
				__METHOD__
			);
		}

		return $this->categoryLinks;
	}

	private function readCategoryStore( $filter = [] ) {
		$params = new ReaderParams( [
			ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE,
			ReaderParams::PARAM_FILTER => $filter,
			ReaderParams::PARAM_SORT => [],
			ReaderParams::PARAM_QUERY => '',
			ReaderParams::PARAM_START => 0
		] );
		return $this->categoryStore->getReader()->read( $params );
	}
}
