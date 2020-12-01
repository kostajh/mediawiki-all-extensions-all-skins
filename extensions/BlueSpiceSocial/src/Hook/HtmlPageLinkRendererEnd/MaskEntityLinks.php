<?php

namespace BlueSpice\Social\Hook\HtmlPageLinkRendererEnd;

use BlueSpice\Hook\HtmlPageLinkRendererEnd;
use BlueSpice\Social\Entity;

/**
 * Mask Links to the entiy source titles (BSSocial:<id>)
 */
class MaskEntityLinks extends HtmlPageLinkRendererEnd {

	protected function skipProcessing() {
		if ( !$this->target || $this->target->getNamespace() !== NS_SOCIALENTITY ) {
			return true;
		}
		if ( strpos( \HtmlArmor::getHtml( $this->text ), "SocialEntity:" ) !== 0 ) {
			return true;
		}
		$entityFactory = $this->getServices()->getService(
			'BSEntityFactory'
		);
		$entity = $entityFactory->newFromSourceTitle( $this->target );
		if ( !$entity instanceof Entity ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$entityFactory = $this->getServices()->getService(
			'BSEntityFactory'
		);
		/** @var Entity $entity */
		$entity = $entityFactory->newFromSourceTitle( $this->target );
		// TODO: Every Entity should have its own mask for its source title
		$msg = $this->getContext()->msg(
			$entity->getConfig()->get( 'TypeMessageKey' )
		);

		$this->text = new \HtmlArmor(
			"({$msg->plain()}) {$entity->getHeader()}"
		);
		return true;
	}
}
