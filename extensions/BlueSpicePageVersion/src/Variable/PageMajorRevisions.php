<?php

namespace BS\PageVersion\Variable;

use BS\PageVersion\RevisionCounter;

class PageMajorRevisions extends Base {

	/**
	 * @return string
	 */
	public function getValue() {
		return RevisionCounter::instance( $this->parser->getTitle() )->getMajorRevisionCount();
	}
}
