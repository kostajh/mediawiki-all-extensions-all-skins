<?php

namespace BlueSpice\Social\Tags\Hook\BSEntitySetValuesByObject;

use BlueSpice\Hook\BSEntitySetValuesByObject;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Entity\Action;
use MediaWiki\MediaWikiServices;

class SetTags extends BSEntitySetValuesByObject {

	protected function checkEntity() {
		if ( !$this->entity->getConfig( 'IsTagable' ) || $this->entity instanceof Action ) {
			return false;
		}
		if ( $this->entity->hasParent() ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		if ( !$this->entity instanceof Entity ) {
			return true;
		}
		if ( empty( $this->entity->get( 'tags', [] ) ) ) {
			$this->entity->set( 'tags', [] );
		}
		if ( !$this->checkEntity() ) {
			return true;
		}
		if ( empty( $this->data->tags ) ) {
			$this->data->tags = [];
		}
		if ( $this->entity->getConfig( 'ForceRelatedTitleTag' ) && $this->entity->getRelatedTitle() ) {
			$this->data->tags = array_values( array_unique( array_merge(
				$this->data->tags,
				[ $this->entity->getRelatedTitle()->getFullText() ]
			) ) );
		}
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSSocialTagsBeforeSetTags', [
			$this->entity,
			&$this->data->tags
		] );
		$this->entity->set( 'tags', $this->data->tags );

		return true;
	}
}
