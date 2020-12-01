<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class Component extends LWItem {

	function loadFromId( $prefixedId ) {
		$this->fromId( $prefixedId, EntityIDs::pid( 'qComponent' ) );
	}
	function loadFromItemId( $entityId ) {
		$this->fromItemId( $entityId, EntityIDs::pid( 'qComponent' ) );
	}

	function updatedData( $clearCache = false ) {
		global $wgLang;

		$oldRev = $this->data['revid'];
		if ( $this->updateContent( $oldRev ) || $clearCache ) {
			$name = $this->getEntity()->getLabel( $wgLang->getCode() );
			$topics = $this->getItems( EntityIDs::pid( 'pTopic' ), EntityIDs::pid( 'qTopic' ) );

			if ( !$name ) {
				$name = $this->getPrefixedId();
			}

			$this->data = [
				'id' => $this->getPrefixedId(),
				'name' => $name,
				'topic' => count( $topics ) > 0 ? $topics[0] : '',
				'updated' => date( 'Y-m-d H:i:s' ).' UTC',
				'revid' => $this->getRevision(),
				'oldrevid' => $oldRev,
			];
		}

		return $this->data;
	}

	protected function keyName() {
		return 'component';
	}

	public static function getComponentData( $clearCache = false ) {
		$component = new Component( 'empty', null );
		return $component->getAllData( $clearCache );
	}

}
