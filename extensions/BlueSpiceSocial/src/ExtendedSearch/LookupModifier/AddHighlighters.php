<?php

namespace BlueSpice\Social\ExtendedSearch\LookupModifier;

use BS\ExtendedSearch\Source\LookupModifier\Base as LookupModifierBase;

class AddHighlighters extends LookupModifierBase {
	public function apply() {
		$this->oLookup->addHighlighter( 'entitydata.renderedtext' );
		$this->oLookup->addHighlighter( 'entitydata.text' );
		$this->oLookup->addHighlighter( 'entitydata.teaser' );
	}

	public function undo() {
		$this->oLookup->removeHighlighter( 'entitydata.renderedtext' );
		$this->oLookup->removeHighlighter( 'entitydata.text' );
		$this->oLookup->removeHighlighter( 'entitydata.teaser' );
	}

}
