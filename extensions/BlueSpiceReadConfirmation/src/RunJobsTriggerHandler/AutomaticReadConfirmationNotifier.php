<?php

namespace BlueSpice\ReadConfirmation\RunJobsTriggerHandler;

use BlueSpice\ReadConfirmation\IMechanism;
use BlueSpice\ReadConfirmation\MechanismFactory;
use BlueSpice\RunJobsTriggerHandler;
use MediaWiki\MediaWikiServices;

class AutomaticReadConfirmationNotifier extends RunJobsTriggerHandler {

	/**
	 * @return \Status
	 */
	protected function doRun() {
		$status = \Status::newGood();
		$this->getReadConfirmationMechanism()->autoNotify();
		return $status;
	}

	/**
	 * @return IMechanism
	 */
	private function getReadConfirmationMechanism() {
		/** @var MechanismFactory $factory */
		$factory = MediaWikiServices::getInstance()->getService(
			'BSReadConfirmationMechanismFactory'
		);

		return $factory->getMechanismInstance();
	}
}
