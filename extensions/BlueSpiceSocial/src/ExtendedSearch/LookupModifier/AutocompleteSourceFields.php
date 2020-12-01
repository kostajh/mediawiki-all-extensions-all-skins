<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;

class AutocompleteSourceFields extends LookupModifierBase {

	public function apply() {
		$this->oLookup->addSourceField( [
			'entitydata.id', 'entitydata.header', 'entitydata.type', 'prefixed_title'
		] );
	}

	public function undo() {
	}

}
