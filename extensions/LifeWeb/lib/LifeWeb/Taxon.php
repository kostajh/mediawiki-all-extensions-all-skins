<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class Taxon extends LWItem {

	function loadFromId( $prefixedId ) {
		$this->fromId( $prefixedId, EntityIDs::pid( 'qTaxon' ) );
	}
	function loadFromItemId( $entityId ) {
		$this->fromItemId( $entityId, EntityIDs::pid( 'qTaxon' ) );
	}

	function keyName() {
		return 'taxon';
	}

	function updatedData( $clearCache = false ) {
		$oldRev = $this->data['revid'];
		if ( $this->updateContent( $oldRev ) || $clearCache ) {
			$images = $this->getStrings( EntityIDs::pid( 'pImage' ) );
			$names = $this->getStrings( EntityIDs::pid( 'pLatin' ) );
			$topics = $this->getItems( EntityIDs::pid( 'pTopic' ), EntityIDs::pid( 'qTopic' ) );
			$degrees = $this->getItems( EntityIDs::pid( 'pDegree' ), EntityIDs::pid( 'qDegree' ) );
			$parent = $this->getItems( EntityIDs::pid( 'pParent' ), EntityIDs::pid( 'qTaxon' ) );

			foreach ( $images as &$image ) {
				$image = '/'.$image;
			}
			$this->data = [
				'id' => $this->getPrefixedId(),
				'name' => count( $names ) > 0 ? $names[0] : '',
				'topic' => count( $topics ) > 0 ? $topics[0] : null,
				'images' => $images,
				'characters' => $this->getItems( EntityIDs::pid( 'pHasCharacter' ),
					EntityIDs::pid( 'qCharacter' ) ),
				'degree' => count( $degrees ) > 0 ? $degrees[0] : '',
				'parentTaxon' => count( $parent ) > 0 ? $parent[0] : '',
				'updated' => date( 'Y-m-d H:i:s' ).' UTC',
				'revid' => $this->getRevision(),
				'oldrevid' => $oldRev,
			];
		}

		return $this->data;
	}

	public static function getTaxonData( $clearCache = false ) {
		$taxon = new Taxon( 'empty', null );
		return $taxon->getAllData( $clearCache );
	}

}
