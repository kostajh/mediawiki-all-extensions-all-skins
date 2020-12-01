<?php

namespace BlueSpice\Social\ExtendedSearch\Formatter\Internal;

use BlueSpice\Social\Entity\Page;

class PageFormatter extends EntityFormatter {
	/**
	 *
	 * @param Page $entity
	 * @param array &$result
	 * @param \Elastica\Result $resultObject
	 */
	public function __construct( $entity, array &$result, $resultObject ) {
		parent::__construct( $entity, $result, $resultObject );
	}

	/**
	 *
	 */
	public function format() {
		parent::format();
	}
}
