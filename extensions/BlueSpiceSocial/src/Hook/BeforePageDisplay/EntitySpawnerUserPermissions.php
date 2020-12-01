<?php

namespace BlueSpice\Social\Hook\BeforePageDisplay;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityConfig;

class EntitySpawnerUserPermissions extends BeforePageDisplay {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$activityStreamPermissions = [];
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		$configFactory = $this->getServices()->getService( 'BSEntityConfigFactory' );
		foreach ( $registry->getAllKeys() as $type ) {
			$entityConfig = $configFactory->newFromType( $type );
			if ( !$entityConfig instanceof EntityConfig ) {
				continue;
			}
			$entity = $this->getServices()->getService( 'BSEntityFactory' )->newFromObject(
				(object)[ 'type' => $type ]
			);
			if ( !$entity ) {
				continue;
			}

			if ( !$entity instanceof Entity ) {
				continue;
			}

			if ( !$entity->getConfig()->get( 'IsSpawnable' ) ) {
				continue;
			}

			$status = $entity->userCan( 'create', $this->out->getUser() );

			if ( !$status->isOK() ) {
				continue;
			}

			$activityStreamPermissions[] = $type;
		}

		$this->out->addJsConfigVars(
			'bsgSocialUserSpawnerEntities',
			$activityStreamPermissions
		);

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}

		return false;
	}
}
