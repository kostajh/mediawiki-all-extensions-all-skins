<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class ItemDeletedException extends \MWException {
	public function __construct() {
		parent::__construct( print_r( 'Item has been deleted', true ) );
	}
}

abstract class LWItem {

	/// @type \Wikibase\EntityContent
	protected $itemContent;
	protected $itemId;
	protected $item;

	protected $data = [
		'revid' => -1
	];

	function __construct( $type, $options ) {
		switch ( $type ) {
			case 'entityId' :
				$this->loadFromItemId( $options['entityId'] );
				break;
			case 'empty' :
				break;
			case 'prefixedId' :
				$this->loadFromId( $options['prefixedId'] );
				break;
		}
	}

	abstract protected function loadFromId( $id );
	protected function fromId( $id, $pidInstanceType ) {
		$itemID = new \Wikibase\DataModel\Entity\ItemId( $id );
		$this->loadFromItemId( $itemID, $pidInstanceType );
	}

	abstract protected function loadFromItemId( $itemId );
	protected function fromItemId( $itemId, $pidInstanceType ) {
		$this->itemId = $itemId;

		/*
		if (!\Wikibase\EntityLookup::hasEntity($itemId)) {
			throw new Exception('Invalid ID.');
		}
		*/

		if ( !self::instanceOfCheck( $itemId, $pidInstanceType, EntityIDs::pid( 'pInstanceOf' ) ) ) {
			throw new \MWException( 'Is not an instance.' );
		}

		$this->itemContent = \Wikibase\EntityContentFactory::singleton()->getFromId( $this->itemId );
		if ( $this->itemContent === null ) {
			throw new \MWException( 'Invalid ID.' );
		}
		$this->item = $this->itemContent->getEntity();
	}

	function getLabel( $lang = 'en-gb' ) {
		return $this->item->getLabel( $lang );
	}
	function getPrefixedId() {
		return $this->itemId->getPrefixedId();
	}
	function getEntity() {
		return $this->item;
	}
	function getRevision() {
		return $this->itemContent->getTitle()->getLatestRevID();
	}
	function updateContent( $fromRev ) {
		$updatedContent = \Wikibase\EntityContentFactory::singleton()->getFromId( $this->itemId );

		// Check if the item has been deleted
		if ( !$updatedContent ) {
			throw new ItemDeletedException();
		}

		$newRev = $updatedContent->getTitle()->getLatestRevID();
		if ( $fromRev != $newRev ) {
			$this->itemContent = $updatedContent;
			$this->item = $updatedContent->getEntity();
			return true;
		}
		return false;
	}

	/**
	 * @param string $pidProperty Property whose value is asked
	 * @return array|null
	 */
	function getStrings( $pidProperty ) {
		$strings = [];

		$claims = $this->item->getClaims();
		foreach ( $claims as $claim ) {
			$snak = $claim->getMainSnak();
			$snakID = $snak->getPropertyId();

			if ( strtolower( $snakID ) == strtolower( $pidProperty ) ) {
				$snakValue = $snak->getDataValue();
				if ( $snakValue instanceof \DataValues\StringValue ) {
					$strings[] = $snakValue->getValue();
				}
			}

		}

		return $strings;
	}

	/**
	 * @param string $pidProperty Property whose value is asked
	 * @param string $pidInstanceType PID of the required instance
	 * @return array|null
	 */
	function getItems( $pidProperty, $pidInstanceType ) {
		$values = [];

		$claims = $this->item->getClaims();
		foreach ( $claims as $claim ) {
			$snak = $claim->getMainSnak();
			$snakID = $snak->getPropertyId();

			if ( strtolower( $snakID ) == strtolower( $pidProperty ) ) {
				$snakValue = $snak->getDataValue();
				if ( $snakValue instanceof \Wikibase\DataModel\Entity\EntityIdValue ) {
					if ( self::instanceOfCheck( $snakValue->getEntityId(), $pidInstanceType ) ) {
						$values[] = $snakValue->getEntityId()->getPrefixedId();
					}
				}
			}
		}

		return $values;
	}

	public static function instanceOfCheck( $entityID, $pidInstanceType ) {
		$eidInstance = \Wikibase\EntityID::newFromPrefixedId( $pidInstanceType );
		$eidInstanceOf = \Wikibase\EntityID::newFromPrefixedId( EntityIDs::pid( 'pInstanceOf' ) );

		$entityContent = \Wikibase\EntityContentFactory::singleton()->getFromId( $entityID );
		if ( $entityContent === null ) {
			return false;
		}
		$entity = $entityContent->getEntity();

		$isInstance = false;
		$claims = $entity->getClaims();
		foreach ( $claims as $claim ) {
			$snak = $claim->getMainSnak();
			$snakID = $snak->getPropertyId();

			if ( $snakID == $eidInstanceOf ) {
				if ( $snak instanceof \Wikibase\PropertyValueSnak ) {
					$snakValue = $snak->getDataValue();
					if ( $snakValue instanceof \Wikibase\DataModel\Entity\EntityIdValue ) {
						if ( $snakValue->getEntityId() == $eidInstance ) {
							$isInstance = true;
							break;
						}
					}
				} else {
					// Not a P-V snak
				}

			}
		}

		return $isInstance;
	}

	abstract function updatedData( $clearCache = false );

	abstract protected function keyName();
	protected function getAllData( $clearCache = false ) {
		global $wgLang;

		$foundInCache = true;
		$debugMessages = '';

		$cache = wfGetMainCache();

		$name = $this->keyName();
		$cacheKey = wfMemcKey( $name, 'Items', $wgLang->getCode() );
		$items = $cache->get( $cacheKey );

		$cacheKeyMax = wfMemcKey( $name, 'ItemsMaxpage', $wgLang->getCode() );
		$maxpage = $cache->get( $cacheKeyMax );

		$dbr = wfGetDB( DB_REPLICA );

		if ( $items === false || $clearCache ) {
			// Nothing cached
			$foundInCache = false;
			$items = [];

			$res = $dbr->select(
				'page',
				[ 'page_id', 'page_title' ],
				'page_content_model = \'wikibase-item\'',
				__METHOD__,
				[ 'ORDER BY' => 'page_id ASC' ]
			);
			foreach ( $res as $row ) {
				try {

					$items[] = new $this( 'prefixedId', [ 'prefixedId' => $row->page_title ] );

				} catch ( \Exception $e ) {
				}
				$maxpage = $row->page_id;
			}

		} else {

			// Cached items are here (up to $maxpage); check for new items

			if ( $maxpage === false ) {
				$maxpage = 0;
				$items = [];
			}

			$res = $dbr->select(
				'page',
				[ 'page_id', 'page_content_model', 'page_title' ],
				'page_id > '.$maxpage.' AND page_content_model = \'wikibase-item\'',
				__METHOD__,
				[ 'ORDER BY' => 'page_id ASC' ]
			);

			foreach ( $res as $row ) {
				try {
					$items[] = new $this( 'prefixedId', [ 'prefixedId' => $row->page_title ] );
				} catch ( \Exception $e ) {
				}
				$maxpage = $row->page_id;
			}

		}

		// Build the data array
		$data = [];
		$deletedCount = 0;
		foreach ( $items as $key => $item ) {
			try {
				$data[] = $item->updatedData( $clearCache );
			} catch ( ItemDeletedException $e ) {
				$deletedCount++;
				unset( $items[$key] );
			}
		}

		// Update the cache
		$cache->set( $cacheKeyMax, $maxpage, 7200 );
		$cache->set( $cacheKey, $items, 7200 );

		return [
			'data' => $data,
			'ok' => true,
			'hotCache' => $foundInCache,
			'count' => count( $data ),
			'maxpage' => $maxpage,
			'debugMessages' => $debugMessages,
			'deletedEntries' => $deletedCount,
		];
	}

}
