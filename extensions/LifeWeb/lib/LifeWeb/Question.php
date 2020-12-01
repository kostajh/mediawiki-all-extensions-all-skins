<?php

/**
 * @licence GNU GPL v2+
 * @author Simon A. Eugster <simon.eu@gmail.com>
 */

namespace LifeWeb;

class Question extends LWItem {

	function loadFromId( $prefixedId ) {
		$this->fromId( $prefixedId, EntityIDs::pid( 'qQuestion' ) );
	}
	function loadFromItemId( $entityId ) {
		$this->fromItemId( $entityId, EntityIDs::pid( 'qQuestion' ) );
	}

	function updatedData( $clearCache = false ) {
		global $wgLang;

		$oldRev = $this->data['revid'];
		if ( $this->updateContent( $oldRev ) || $clearCache ) {
			$name = $this->getEntity()->getLabel( $wgLang->getCode() );
			if ( !$name ) {
				$name = $this->getPrefixedId();
			}

			$difficulties = $this->getItems( EntityIDs::pid( 'pDifficulty' ),
				EntityIDs::pid( 'qDifficulty' ) );
			$parentCharacters = $this->getItems( EntityIDs::pid( 'pParentCharacter' ),
				EntityIDs::pid( 'qCharacter' ) );
			$components = $this->getItems( EntityIDs::pid( 'pComponent' ),
				EntityIDs::pid( 'qComponent' ) );
			$times = $this->getStrings( EntityIDs::pid( 'pTime' ) );

			$this->data = [
				'id' => $this->getPrefixedId(),
				'name' => $name,
				'parentCharacter' => count( $parentCharacters ) > 0 ? $parentCharacters[0] : null,
				'component' => count( $components ) > 0 ? $components[0] : null,
				'difficulty' => count( $difficulties ) > 0 ? $difficulties[0] : null,
				'timeSec' => count( $times ) > 0 ? $times[0] : null,
				'updated' => date( 'Y-m-d H:i:s' ).' UTC',
				'revid' => $this->getRevision(),
				'oldrevid' => $oldRev,
			];
		}

		return $this->data;
	}

	protected function keyName() {
		return 'question';
	}

	public static function getQuestionData( $clearCache = false ) {
		$question = new Question( 'empty', null );
		return $question->getAllData( $clearCache );
	}
}
