<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class EntityIDs {

	function __construct() {
		$this->eids = [
			'qTopic' => Query::searchEntity( 'en-gb', 'Topic' ),
			'qTaxon' => Query::searchEntity( 'en-gb', 'Taxon' ),
			'qQuestion' => Query::searchEntity( 'en-gb', 'Question' ),
			'qCharacter' => Query::searchEntity( 'en-gb', 'Character' ),
			'qComponent' => Query::searchEntity( 'en-gb', 'Component' ),
			'qCollection' => Query::searchEntity( 'en-gb', 'Collection' ),
			'qDegree' => Query::searchEntity( 'en-gb', 'Taxonomic degree' ),
			'qDifficulty' => Query::searchEntity( 'en-gb', 'Difficulty' ),
			'qEquipment' => Query::searchEntity( 'en-gb', 'Equipment' ),
			'pInstanceOf' => Query::searchEntity( 'en-gb', 'instance of', 'property' ),
			'pTopic' => Query::searchEntity( 'en-gb', 'topic', 'property' ),
			'pHasCharacter' => Query::searchEntity( 'en-gb', 'has character', 'property' ),
			'pComponent' => Query::searchEntity( 'en-gb', 'component', 'property' ),
			'pImage' => Query::searchEntity( 'en-gb', 'image', 'property' ),
			'pTime' => Query::searchEntity( 'en-gb', 'time required [s]', 'property' ),
			'pDegree' => Query::searchEntity( 'en-gb', 'taxon rank', 'property' ),
			'pDifficulty' => Query::searchEntity( 'en-gb', 'difficulty', 'property' ),
			'pLatin' => Query::searchEntity( 'en-gb', 'latin name', 'property' ),
			'pParent' => Query::searchEntity( 'en-gb', 'parent', 'property' ),
			'pParentQuestion' => Query::searchEntity( 'en-gb', 'parent question', 'property' ),
			'pParentCharacter' => Query::searchEntity( 'en-gb', 'refines character', 'property' ),
		];
	}

	public function entityIDs() {
		return $this->eids;
	}

	/**
	 * @param $name
	 * @return \Wikibase\EntityID
	 */
	public static function eid( $name ) {
		$entity = self::instance()->entityIDs()[$name];
		return $entity;
	}

	public static function pid( $name ) {
		$entity = self::instance()->entityIDs()[$name];
		if ( $entity ) {
			return $entity->getPrefixedId();
		} else {
			throw new \MWException( 'Entity "'.$name.'" is not defined.' );
		}
	}

	private $eids = null;
	/**
	 * @return EntityIDs
	 */
	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new EntityIDs();
		}
		return self::$instance;
	}

	private static $instance = null;

}
