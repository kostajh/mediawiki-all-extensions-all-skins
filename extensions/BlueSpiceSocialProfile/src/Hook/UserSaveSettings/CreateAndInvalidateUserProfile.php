<?php
namespace BlueSpice\Social\Profile\Hook\UserSaveSettings;

use BlueSpice\Hook\UserSaveSettings;
use BlueSpice\Social\Profile\Entity\Profile;

class CreateAndInvalidateUserProfile extends UserSaveSettings {
	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$entityFactory = $this->getServices()->getService(
			'BSSocialProfileEntityFactory'
		);
		$entity = $entityFactory->newFromUser( $this->user );

		if ( !$entity instanceof Profile ) {
			return true;
		}

		if ( !$entity->exists() && PHP_SAPI !== 'cli' ) {
			$status = $entity->save();
			return true;
		}
		$entity->invalidateCache();

		return true;
	}
}
