<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;

class EntitySimpleQSFields extends LookupModifierBase {
	public function apply() {
		$queryString = $this->oLookup->getQueryString();

		$fields = [ 'entitydata.header^2' ];
		if ( isset( $queryString['fields'] ) && is_array( $queryString['fields'] ) ) {
			$queryString['fields'] = array_merge( $queryString['fields'], $fields );
		} else {
			$queryString['fields'] = $fields;
		}

		$this->oLookup->setQueryString( $queryString );
	}

	public function undo() {
		$queryString = $this->oLookup->getQueryString();

		if ( isset( $queryString['fields'] ) && is_array( $queryString['fields'] ) ) {
			$queryString['fields'] = array_diff( $queryString['fields'],
					[ 'entitydata.header^2' ] );
		}

		$this->oLookup->setQueryString( $queryString );
	}

}
