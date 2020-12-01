<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class Topic extends LWItem {

	function loadFromId( $prefixedId ) {
		$this->fromId( $prefixedId, EntityIDs::pid( 'qTopic' ) );
	}
	function loadFromItemId( $entityId ) {
		$this->fromItemId( $entityId, EntityIDs::pid( 'qTopic' ) );
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
		return 'topic';
	}

	public static function getTopicData( $clearCache = false ) {
		$topic = new Topic( 'empty', null );
		return $topic->getAllData( $clearCache );
	}

}
