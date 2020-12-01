<?php

namespace BlueSpice\Social\Rating\Hook\BSEntityGetFullData;

use BlueSpice\Hook\BSEntityGetFullData;
use BlueSpice\Social\Entity;

class AddRating extends BSEntityGetFullData {

	protected function checkEntity() {
		if ( !$this->entity->getConfig()->get( 'IsRateable' ) ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		if ( !$this->entity instanceof Entity ) {
			return false;
		}
		$this->data[ 'ratingcount' ] = 0;
		if ( !$this->checkEntity() ) {
			return true;
		}

		if ( !$this->entity->exists() ) {
			return true;
		}
		$factory = $this->getServices()->getService( 'BSRatingFactoryEntity' );
		$ratingItem = $factory->newFromEntity( $this->entity );
		if ( !$ratingItem ) {
			return true;
		}
		$this->data[ 'ratingcount' ] = $ratingItem->getRatingSet()->getTotal();
		return true;
	}
}
