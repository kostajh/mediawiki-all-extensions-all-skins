<?php

namespace BlueSpice\Social\Tags\Hook\BSEntityGetFullData;

use BlueSpice\Hook\BSEntityGetFullData;
use BlueSpice\Social\Entity;

class AddTags extends BSEntityGetFullData {

	protected function checkEntity() {
		if ( !$this->entity instanceof Entity ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		if ( !$this->checkEntity() ) {
			return true;
		}

		if ( empty( $this->data['tags'] ) ) {
			$this->data['tags'] = [];
		}
		if ( empty( $this->entity->get( 'tags', [] ) ) ) {
			return true;
		}
		$this->data['tags'] = $this->entity->get( 'tags', [] );
		return true;
	}
}
