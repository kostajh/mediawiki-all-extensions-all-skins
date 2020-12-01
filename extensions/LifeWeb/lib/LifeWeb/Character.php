<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class Character extends LWItem {

	function loadFromId( $prefixedId ) {
		$this->fromId( $prefixedId, EntityIDs::pid( 'qCharacter' ) );
	}
	function loadFromItemId( $entityId ) {
		$this->fromItemId( $entityId, EntityIDs::pid( 'qCharacter' ) );
	}

	protected function keyName() {
		return 'character';
	}

	function updatedData( $clearCache = false ) {
		global $wgLang;

		$oldRev = $this->data['revid'];
		if ( $this->updateContent( $oldRev ) || $clearCache ) {
			$images = $this->getStrings( EntityIDs::pid( 'pImage' ) );
			$questions = $this->getItems( EntityIDs::pid( 'pParentQuestion' ),
				EntityIDs::pid( 'qQuestion' ) );

			$name = $this->getEntity()->getLabel( $wgLang->getCode() );
			if ( !$name ) {
				$name = $this->getPrefixedId();
			}

			$this->data = [
				'id' => $this->getPrefixedId(),
				'name' => $name,
				// 'description' => $this->getEntity()->getDescription($wgLang->getCode()),
				'images' => $images,
				'parentQuestion' => count( $questions ) > 0 ? $questions[0] : null,
				'updated' => date( 'Y-m-d H:i:s' ).' UTC',
				'revid' => $this->getRevision(),
				'oldrevid' => $oldRev,
			];
		}

		return $this->data;
	}

	public static function getCharacterData( $clearCache = false ) {
		$char = new Character( 'empty', null );
		return $char->getAllData( $clearCache );
	}

}
