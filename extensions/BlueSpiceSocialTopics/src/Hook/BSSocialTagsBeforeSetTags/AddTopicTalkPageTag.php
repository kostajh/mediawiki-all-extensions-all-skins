<?php
namespace BlueSpice\Social\Topics\Hook\BSSocialTagsBeforeSetTags;

use BlueSpice\Social\Tags\Hook\BSSocialTagsBeforeSetTags;
use BlueSpice\Social\Topics\Entity\Topic;

class AddTopicTalkPageTag extends BSSocialTagsBeforeSetTags {
	protected function skipProcessing() {
		if ( !$this->entity instanceof Topic ) {
			return true;
		}
		if ( !$this->entity->getRelatedTitle()->exists() ) {
			return true;
		}

		return parent::skipProcessing();
	}

	protected function doProcess() {
		$this->tags = array_values( array_unique( array_merge( $this->tags, [
			$this->entity->getRelatedTitle()->getOtherPage()->getFullText()
		] ) ) );
		return true;
	}

}
