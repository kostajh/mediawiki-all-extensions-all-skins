<?php

namespace BlueSpice\Social\Resolve\Hook\BSEntitySetValuesByObject;

use BlueSpice\Hook\BSEntitySetValuesByObject;
use BlueSpice\Social\Entity;

class AddResolve extends BSEntitySetValuesByObject {

	protected function checkEntity() {
		if ( !$this->entity->getConfig( 'IsResolvable' ) ) {
			return false;
		}
		if ( $this->entity->hasParent() ) {
			return false;
		}
		if ( !$this->entity->exists() ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		if ( !$this->entity instanceof Entity ) {
			return true;
		}
		if ( !$this->checkEntity() ) {
			return true;
		}
		if ( !isset( $this->data->resolved ) ) {
			return true;
		}
		$this->entity->set( 'resolved', $this->data->resolved ? true : false );

		return true;
	}
}
