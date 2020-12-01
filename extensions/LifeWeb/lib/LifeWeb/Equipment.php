<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class Equipment extends LWItem {

	function loadFromId( $prefixedId ) {
		$this->fromId( $prefixedId, EntityIDs::pid( 'qEquipment' ) );
	}
	function loadFromItemId( $entityId ) {
		$this->fromItemId( $entityId, EntityIDs::pid( 'qEquipment' ) );
	}

	function updatedData( $clearCache = false ) {
		global $wgLang;

		$oldRev = $this->data['revid'];
		if ( $this->updateContent( $oldRev ) || $clearCache ) {
			$name = $this->getEntity()->getLabel( $wgLang->getCode() );
			if ( !$name ) {
				$name = $this->getPrefixedId();
			}

			$this->data = [
				'id' => $this->getPrefixedId(),
				'name' => $name,
				'updated' => date( 'Y-m-d H:i:s' ).' UTC',
				'revid' => $this->getRevision(),
				'oldrevid' => $oldRev,
			];
		}

		return $this->data;
	}

	protected function keyName() {
		return 'equipment';
	}

	public static function getEquipmentData( $clearCache = false ) {
		$equipment = new Equipment( 'empty', null );
		return $equipment->getAllData( $clearCache );
	}

}
