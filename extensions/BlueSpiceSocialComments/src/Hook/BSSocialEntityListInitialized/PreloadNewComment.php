<?php

namespace BlueSpice\Social\Comments\Hook\BSSocialEntityListInitialized;

use BlueSpice\Social\Comments\Entity\Comment;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityListContext\Children;
use BlueSpice\Social\Hook\BSSocialEntityListInitialized;
use BlueSpice\Social\Renderer\EntityList;

class PreloadNewComment extends BSSocialEntityListInitialized {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( !$this->entityList->getContext() instanceof Children ) {
			return true;
		}
		$parentEntity = $this->entityList->getContext()->getParent();
		if ( !$parentEntity instanceof Entity || !$parentEntity->exists() ) {
			return true;
		}
		if ( !$parentEntity->getConfig()->get( 'CanHaveChildren' ) ) {
			return true;
		}

		$comment = $this->getServices()->getService( 'BSEntityFactory' )->newFromObject(
			$this->getRawComment()
		);
		if ( !$comment instanceof Comment ) {
			return true;
		}

		$status = $comment->userCan(
			'create',
			$this->entityList->getContext()->getUser()
		);
		if ( !$status->isOk() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		// TODO: Check if comments are available in children - for now there are
		// only comments available...
		$this->args[ EntityList::PARAM_PRELOADED_ENTITIES ][]
			= $this->getRawComment();
	}

	/**
	 *
	 * @return array
	 */
	protected function getRawComment() {
		$parentEntity = $this->entityList->getContext()->getParent();
		return (object)[
			Comment::ATTR_TYPE => Comment::TYPE,
			Comment::ATTR_PARENT_ID => $parentEntity->get( Entity::ATTR_ID )
		];
	}

}
