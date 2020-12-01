<?php

namespace BlueSpice\Social\ExtendedSearch\Crawler;

use MediaWiki\MediaWikiServices;

class Entity extends \BS\ExtendedSearch\Source\Crawler\Base {
	protected $sJobClass = "\\BlueSpice\\Social\\ExtendedSearch\\Job\\Entity";

	protected $entities = [];
	protected $titles = [];
	protected $ordered = [];

	/**
	 *
	 */
	public function crawl() {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection(
			DB_REPLICA
		);
		$res = $dbr->select(
			'page',
			'*',
			$this->makeQueryConditions()
		);
		$aTitles = \TitleArray::newFromResult( $res );

		$this->makeEntities( $aTitles );
		$this->order();
		$this->crawlBackwards( $this->ordered );
	}

	/**
	 *
	 * @return array
	 */
	public function makeQueryConditions() {
		return [ 'page_namespace' => NS_SOCIALENTITY ];
	}

	/**
	 *
	 * @param \Title[] $titles
	 */
	protected function makeEntities( $titles ) {
		// It can be a very lengthy process since we are reading out
		// the content of each page to determine parent_id.
		// Would be nice to have these relationships ( and other metadata )
		// somewhere else (a table), and only the actual content in wikipages
		$services = MediaWikiServices::getInstance();
		foreach ( $titles as $title ) {
			$entity = $services->getService( 'BSEntityFactory' )
				->newFromSourceTitle( $title );
			if ( !$entity instanceof \BlueSpice\Social\Entity || !$entity->exists() ) {
				continue;
			}
			$id = $entity->get( \BlueSpice\Social\Entity::ATTR_ID );
			$this->entities[$id] = $entity->get( \BlueSpice\Social\Entity::ATTR_PARENT_ID, 0 );
			$this->titles[$id] = $title;
		}
	}

	/**
	 *
	 */
	protected function order() {
		$this->addRoots();
		foreach ( $this->ordered as $eid => &$children ) {
			$this->makeChildren( $children, $eid );
		}
		ksort( $this->ordered );
	}

	/**
	 * Add first layer of entities - the ones with no parent
	 * to define the entry point for the structure
	 */
	protected function addRoots() {
		foreach ( $this->entities as $id => $parent ) {
			if ( $parent === 0 ) {
				$this->ordered[$id] = [];
			}
		}
	}

	/**
	 *
	 * @param array &$target
	 * @param int $eid
	 */
	protected function makeChildren( &$target, $eid ) {
		foreach ( $this->entities as $id => $parent ) {
			if ( $parent === $eid ) {
				$target[$id] = [];
				$this->makeChildren( $target[$id], $id );
			}
		}
	}

	/**
	 * Crawls first children and then parents
	 * @param array $entities
	 */
	protected function crawlBackwards( $entities ) {
		foreach ( $entities as $parent => $children ) {
			if ( count( $children ) > 0 ) {
				$this->crawlBackwards( $children );
			}
			$this->addToJobQueue( $this->titles[$parent] );
		}
	}
}
