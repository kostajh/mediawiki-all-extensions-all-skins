<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;

/**
 * This class adds a field to the filterable fields, by adding aggregation
 * on which filters are based
 */
class EntityTypeAggregation extends LookupModifierBase {
	public function apply() {
		$this->oLookup->setBucketTermsAggregation( 'entitydata.type' );
	}

	public function undo() {
		$this->oLookup->removeBucketTermsAggregation( 'entitydata.type' );
	}

}
