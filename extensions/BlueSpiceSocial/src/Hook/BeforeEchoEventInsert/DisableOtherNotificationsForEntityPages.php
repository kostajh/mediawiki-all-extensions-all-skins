<?php

namespace BlueSpice\Social\Hook\BeforeEchoEventInsert;

use BlueSpice\EchoConnector\Hook\BeforeEchoEventInsert;

class DisableOtherNotificationsForEntityPages extends BeforeEchoEventInsert {

	/**
	 * Disabled all other notifications on entity pages
	 * except ones emitted by BSSocial
	 *
	 * @return bool
	 */
	protected function doProcess() {
		if ( !$this->event->getTitle() ) {
			return true;
		}
		if ( $this->event->getTitle()->getNamespace() !== NS_SOCIALENTITY ) {
			return true;
		}
		$entity = $this->getServices()->getService( 'BSEntityFactory' )->newFromSourceTitle(
			$this->event->getTitle()
		);
		if ( !$entity || !$entity->exists() ) {
			return true;
		}

		if ( $this->event->getExtraParam( 'social-notification', false ) ) {
			return true;
		}

		return false;
	}
}
