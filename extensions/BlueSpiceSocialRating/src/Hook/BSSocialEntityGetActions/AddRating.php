<?php

namespace BlueSpice\Social\Rating\Hook\BSSocialEntityGetActions;

use BlueSpice\Social\Entity;
use BlueSpice\Social\Hook\BSSocialEntityGetActions;

class AddRating extends BSSocialEntityGetActions {

	protected function doProcess() {
		$factory = $this->getServices()->getService( 'BSRatingFactoryEntity' );
		$ratingItem = $factory->newFromEntity( $this->oEntity );

		$this->aActions['rating'] = $ratingItem->getTagData();
		return true;
	}

	protected function skipProcessing() {
		if ( !$this->oEntity instanceof Entity ) {
			return true;
		}
		if ( !$this->oEntity->getConfig()->get( 'IsRateable' ) ) {
			return true;
		}
		if ( !$this->oEntity->exists() ) {
			return true;
		}

		return false;
	}
}
