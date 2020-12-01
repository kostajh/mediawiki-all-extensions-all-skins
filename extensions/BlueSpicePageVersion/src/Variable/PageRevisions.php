<?php

namespace BS\PageVersion\Variable;

use BS\PageVersion\RevisionCounter;

class PageRevisions extends Base {

	/**
	 * @return string
	 */
	public function getValue() {
		return RevisionCounter::instance( $this->parser->getTitle() )->getRevisionCount();
	}
}
