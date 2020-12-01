<?php

namespace BlueSpice\ReadConfirmation;

use MediaWiki\MediaWikiServices;

class TriggerRegistration {

	public static function addNotificationTrigger() {
		/** @var MechanismFactory $readConfirmationsFactory */
		$readConfirmationsFactory = MediaWikiServices::getInstance()
			->getService( 'BSReadConfirmationMechanismFactory' );

		$readConfirmationsFactory->getMechanismInstance()->wireUpNotificationTrigger();
	}
}
