<?php

namespace BlueSpice\Social\Resolve\Hook\BSEntityGetFullData;

use BlueSpice\Hook\BSEntityGetFullData;
use BlueSpice\Social\Entity;

class AddResolve extends BSEntityGetFullData {

	protected function checkEntity() {
		if ( !$this->entity->getConfig()->get( 'IsResolvable' ) ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		if ( !$this->entity instanceof Entity ) {
			return false;
		}
		$this->data[ 'resolved' ] = false;
		if ( !$this->checkEntity() ) {
			return true;
		}

		if ( !$this->entity->exists() ) {
			return true;
		}
		$factory = $this->getServices()->getService( 'BSSocialResolveFactory' );
		$resolveItem = $factory->newFromEntity( $this->entity );
		if ( !$resolveItem ) {
			return true;
		}

		$this->data[ 'resolved' ] = $resolveItem->isResolved();
		return true;
	}
}
